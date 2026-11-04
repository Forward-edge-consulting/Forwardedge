<?php

namespace App\Http\Controllers;

use App\Mail\BuilderFormAutoReply;
use App\Mail\NewsletterWelcomeMail;
use App\Models\Block;
use App\Models\Form;
use App\Rules\TrustedEmailDomain;
use App\Services\FormResponses;
use App\Support\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Entry point for the footer, blog sidebar and page-builder form blocks.
 * Every submission is stored locally. Contacts are imported into Brevo by hand from
 * Admin > Responses, so nothing depends on a third-party API being up.
 */
class NewsletterController extends Controller
{
    public function subscribe(Request $request, FormResponses $responses)
    {
        if (!Turnstile::passes($request)) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => Turnstile::FAILED_MESSAGE], 422);
            }
            throw ValidationException::withMessages(['email' => Turnstile::FAILED_MESSAGE]);
        }

        if (
            $request->expectsJson() ||
            $request->input('source') === 'builder_form' ||
            $request->has('fields') ||
            $request->has('newsletter_form')
        ) {
            return $this->handleDynamicForm($request, $responses);
        }

        $data = $request->validate([
            'email'      => ['required', \App\Support\FormFields::emailRule(), new TrustedEmailDomain()],
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'consent'    => 'nullable',
            'consent_text' => 'nullable|string|max:500',
            'hp_field'   => ['nullable', 'prohibited'],
        ]);

        $fields = [['label' => 'Email', 'name' => 'email', 'type' => 'email', 'value' => $data['email']]];
        if (filled($data['first_name'] ?? null)) {
            array_unshift($fields, ['label' => 'First name', 'name' => 'first_name', 'type' => 'text', 'value' => $data['first_name']]);
        }
        if (filled($data['last_name'] ?? null)) {
            $fields[] = ['label' => 'Last name', 'name' => 'last_name', 'type' => 'text', 'value' => $data['last_name']];
        }

        $message = $this->store($request, $responses, Form::system('newsletter'), $fields, 'newsletter');

        return back()->with('success', $message);
    }

    protected function handleDynamicForm(Request $request, FormResponses $responses)
    {
        if (!$request->has('fields') && $request->has('newsletter_form')) {
            $blockId = $request->input('block_id') ?: array_key_first((array) $request->input('newsletter_form'));
            $request->merge([
                'block_id' => $blockId,
                'fields'   => $this->mapLegacyFieldsToPayload($request, $blockId),
            ]);
        }

        $payload = $request->validate([
            'block_id'          => 'nullable',
            'fields'            => 'required|array|min:1',
            'fields.*.label'    => 'nullable|string|max:120',
            'fields.*.name'     => 'required|string|max:60',
            'fields.*.type'     => 'required|string|in:text,email,tel,textarea',
            'fields.*.value'    => 'nullable|string|max:5000',
            'fields.*.required' => 'nullable|boolean',
            'consent'           => 'nullable',
            'consent_text'      => 'nullable|string|max:500',
            'hp_field'          => ['nullable', 'prohibited'],
        ]);

        $fields = collect($payload['fields'])->all();
        $emailField = collect($fields)->first(fn ($f) => $f['type'] === 'email' && filled($f['value'] ?? null));

        if (!$emailField) {
            throw ValidationException::withMessages(['fields' => 'Please include an email field in your form.']);
        }
        $this->assertTrustedEmail(trim((string) $emailField['value']));

        $block = isset($payload['block_id']) && is_numeric($payload['block_id']) ? Block::find($payload['block_id']) : null;
        $mode = is_array($block?->data) ? ($block->data['email_mode'] ?? 'newsletter') : 'newsletter';

        // Footer / blog widgets and newsletter-mode blocks are signups; other blocks are enquiries.
        $isNewsletter = !$block || $mode === 'newsletter' || empty($mode);
        $form = Form::system($isNewsletter ? 'newsletter' : 'page-forms');

        $message = $this->store($request, $responses, $form, $fields, $isNewsletter ? 'newsletter' : $mode, $block);

        return $request->expectsJson()
            ? response()->json(['status' => 'ok', 'message' => $message])
            : back()->with('success', $message);
    }

    /** Save the response, then send the (optional) welcome mail. Mail failures never lose the lead. */
    protected function store(Request $request, FormResponses $responses, Form $form, array $fields, string $mode, ?Block $block = null): string
    {
        $rows = [];
        $email = null;
        $first = null;
        $full = null;

        foreach ($fields as $field) {
            $value = trim((string) ($field['value'] ?? ''));
            if ($value === '') {
                continue;
            }
            $name = (string) ($field['name'] ?? '');
            $maps = match (true) {
                str_contains($name, 'first') => 'FIRSTNAME',
                str_contains($name, 'last') || str_contains($name, 'surname') => 'LASTNAME',
                $name === 'name' || $name === 'full_name' => 'FULLNAME',
                in_array($field['type'] ?? '', ['tel'], true) || str_contains($name, 'phone') => 'SMS',
                default => '',
            };
            $rows[] = [
                'key'     => $name,
                'label'   => $field['label'] ?: Str::title(str_replace('_', ' ', $name)),
                'type'    => $field['type'] ?? 'text',
                'maps_to' => $maps,
                'value'   => $value,
            ];
            if (($field['type'] ?? '') === 'email' && $email === null) {
                $email = strtolower($value);
            }
            if ($maps === 'FIRSTNAME') {
                $first = $first ?? $value;
            }
            if ($maps === 'FULLNAME') {
                $full = $full ?? $value;
            }
        }

        $lastNames = collect($rows)->firstWhere('maps_to', 'LASTNAME')['value'] ?? '';
        $displayName = $full ?? (trim(($first ?? '') . ' ' . $lastNames) ?: null);

        $given = filter_var($request->input('consent'), FILTER_VALIDATE_BOOLEAN) || filled($request->input('consent_text'));
        $text = $request->input('consent_text') ?: ($given ? $form->setting('consent_text') : null);

        $responses->store($form, $rows, $email, $displayName, $request, ['given' => $given, 'text' => $text]);

        $data = $block && is_array($block->data) ? $block->data : [];

        try {
            if ($mode === 'newsletter') {
                Mail::to($email)->send(new NewsletterWelcomeMail($first ?: $displayName ?: 'there'));
            } elseif (!in_array($mode, ['none', ''], true)) {
                Mail::to($email)->send(new BuilderFormAutoReply(
                    trim((string) ($data['email_subject'] ?? 'Thank you for contacting us')),
                    trim((string) ($data['email_body'] ?? 'We received your submission and will respond shortly.'))
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Form welcome mail failed', ['error' => $e->getMessage()]);
        }

        return $mode === 'newsletter'
            ? 'Thanks for subscribing.'
            : 'Thanks for reaching out. We will be in touch shortly.';
    }

    protected function assertTrustedEmail(string $email): void
    {
        $validator = validator(['email' => $email], ['email' => ['required', \App\Support\FormFields::emailRule(), new TrustedEmailDomain()]]);

        if ($validator->fails()) {
            throw ValidationException::withMessages(['fields' => $validator->errors()->first('email')]);
        }
    }

    protected function mapLegacyFieldsToPayload(Request $request, $blockId): array
    {
        if (!$blockId) {
            return [];
        }

        $values = Arr::get($request->input('newsletter_form'), $blockId, []);
        $meta   = Arr::get($request->input('field_meta'), $blockId, []);
        $fields = [];

        foreach ($meta as $name => $info) {
            $fields[] = [
                'label'    => $info['label'] ?? Str::title(str_replace('_', ' ', $name)),
                'name'     => $name,
                'type'     => $info['type'] ?? 'text',
                'required' => filter_var($info['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'value'    => $values[$name] ?? '',
            ];
        }

        if (empty($fields) && is_array($values)) {
            foreach ($values as $name => $value) {
                $fields[] = ['label' => Str::title(str_replace('_', ' ', $name)), 'name' => $name, 'type' => 'text', 'required' => false, 'value' => $value];
            }
        }

        return $fields;
    }
}
