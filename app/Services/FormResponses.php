<?php

namespace App\Services;

use App\Mail\BuilderFormAutoReply;
use App\Mail\FormResourceMail;
use App\Mail\FormSubmissionNotification;
use App\Models\Blog;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Stores a response locally first. Mail is best-effort: a mail failure must never lose a lead.
 */
class FormResponses
{
    /**
     * @param array $rows   snapshot rows: [key,label,type,maps_to,value]
     * @param array $consent ['given' => bool, 'text' => ?string]
     */
    public function store(Form $form, array $rows, ?string $email, ?string $name, Request $request, array $consent = []): FormSubmission
    {
        [$sourceType, $sourceLabel, $sourceUrl] = $this->describeSource($request);
        $given = (bool) ($consent['given'] ?? false);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'email' => $email,
            'name' => $name,
            'data' => $rows,
            'source_type' => $sourceType,
            'source_label' => $sourceLabel,
            'source_url' => $sourceUrl,
            'consent_given' => $given,
            'consent_text' => $given ? Str::limit((string) ($consent['text'] ?? ''), 500, '') : null,
            'consented_at' => $given ? now() : null,
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip() . '|' . config('app.key')) : null,
        ]);

        if ($form->hasResource() && $email) {
            $submission->forceFill(['download_token' => Str::random(40)])->save();
        }

        // Mail can be slow or fail. It runs after the visitor has their answer, and a failure is recorded, never fatal.
        dispatch(function () use ($form, $submission) {
            if (!FormSubmission::whereKey($submission->id)->exists()) {
                return; // the lead was rolled back (for example a booking slot was lost), so tell nobody
            }
            $this->notify($form, $submission);
            $this->deliverResource($form, $submission);
        })->afterResponse();

        return $submission;
    }

    public function notify(Form $form, FormSubmission $submission): void
    {
        $to = trim((string) ($form->setting('notify_email') ?: config('contact.email')));
        if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($to)->send(new FormSubmissionNotification($form, $submission));
            } catch (\Throwable $e) {
                Log::warning('Form notification failed', ['form' => $form->slug, 'error' => $e->getMessage()]);
            }
        }

        if ($form->setting('auto_reply') && !$form->hasResource() && $submission->email) {
            try {
                Mail::to($submission->email)->send(new BuilderFormAutoReply(
                    Form::defaultSettings()['auto_reply_subject'],
                    Form::defaultSettings()['auto_reply_body'],
                    $this->firstName($submission)
                ));
            } catch (\Throwable $e) {
                Log::warning('Form auto-reply failed', ['form' => $form->slug, 'error' => $e->getMessage()]);
            }
        }
    }

    /** Email the resource. A failure is recorded on the response so it can be resent, and never loses the lead. */
    public function deliverResource(Form $form, FormSubmission $submission): bool
    {
        if (!$form->hasResource() || !$submission->email) {
            return false;
        }

        if (!$submission->download_token) {
            $submission->download_token = Str::random(40);
        }

        try {
            Mail::to($submission->email)->send(new FormResourceMail(
                (string) $form->setting('resource_email_subject'),
                (string) $form->setting('resource_email_body'),
                (string) ($form->setting('resource_title') ?: 'your resource'),
                route('forms.resource', $submission->download_token),
                $this->firstName($submission)
            ));
            $submission->resource_sent_at = now();
            $submission->resource_error = null;
            $sent = true;
        } catch (\Throwable $e) {
            Log::warning('Form resource email failed', ['form' => $form->slug, 'error' => $e->getMessage()]);
            $submission->resource_error = Str::limit($e->getMessage(), 250, '');
            $sent = false;
        }
        $submission->save();

        return $sent;
    }

    /** What the success popup shows. Null when the form has no resource, so the plain message is used. */
    public function popup(Form $form, FormSubmission $submission): ?array
    {
        if (!$form->hasResource() || !$submission->download_token) {
            return null;
        }

        return [
            'title' => (string) $form->setting('popup_title'),
            'message' => str_replace('{email}', (string) $submission->email, (string) $form->setting('popup_message')),
            'resource_title' => (string) ($form->setting('resource_title') ?: 'Download'),
            'download_url' => route('forms.resource', $submission->download_token),
        ];
    }

    protected function firstName(FormSubmission $submission): string
    {
        $name = trim((string) $submission->name);

        return $name !== '' ? Str::before($name, ' ') : 'there';
    }

    /** Work out which page, post or event a response came from. */
    public function describeSource(Request $request): array
    {
        $url = (string) ($request->input('source_url') ?: $request->headers->get('referer', ''));
        if ($url === '') {
            return [null, null, null];
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $trimmed = trim($path, '/');
        $url = Str::limit($path . (parse_url($url, PHP_URL_QUERY) ? '?' . parse_url($url, PHP_URL_QUERY) : ''), 500, '');

        if ($trimmed === '') {
            return ['page', 'Home page', $url];
        }

        if (preg_match('#^blog/([^/]+)$#', $trimmed, $m)) {
            $blog = Blog::where('slug', $m[1])->first();
            return ['blog', $blog ? 'Blog: ' . $blog->title : 'Blog', $url];
        }
        if (preg_match('#^events/([^/]+)#', $trimmed, $m) && $m[1] !== 'register') {
            $event = Event::where('slug', $m[1])->first();
            return ['event', $event ? 'Event: ' . $event->title : 'Events', $url];
        }
        if (preg_match('#^f/([^/]+)$#', $trimmed)) {
            return ['form', 'Shared form link', $url];
        }

        $page = Page::where('slug', $trimmed)->first();
        if ($page) {
            return ['page', 'Page: ' . $page->title, $url];
        }

        return ['page', Str::headline($trimmed), $url];
    }
}
