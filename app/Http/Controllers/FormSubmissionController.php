<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Services\BookingService;
use App\Services\FormResponses;
use Illuminate\Support\Facades\DB;
use App\Support\FormFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class FormSubmissionController extends Controller
{
    public function show(string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_system', false)->firstOrFail();

        return view('user.pages.form_page', compact('form'));
    }

    public function store(Request $request, string $slug, FormResponses $responses, BookingService $bookings)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        if (!$form->isOpen()) {
            return $this->respond($request, 'error', 'This form is no longer accepting responses.', 410);
        }

        // Bots fill hidden fields and submit instantly.
        if ($request->filled('hp_field')) {
            return $this->respond($request, 'ok', $form->setting('success_message'));
        }
        if ($request->filled('_ts')) {
            try {
                $age = time() - (int) Crypt::decryptString($request->input('_ts'));
            } catch (\Throwable) {
                $age = 0;
            }
            if ($age < 2) {
                return $this->respond($request, 'ok', $form->setting('success_message'));
            }
        }
        if (!\App\Support\Turnstile::passes($request)) {
            return $this->respond($request, 'error', \App\Support\Turnstile::FAILED_MESSAGE, 422);
        }

        $fields = $form->fields ?? [];
        $rules = FormFields::rules($fields);
        $consentRequired = (bool) $form->setting('consent_required');
        if ($consentRequired) {
            $rules['consent'] = ['accepted'];
        }

        $validator = validator($request->all(), $rules, [
            'consent.accepted' => 'Please tick the box to continue.',
        ], FormFields::attributeNames($fields));

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }
            throw new ValidationException($validator);
        }

        [$rows, $email, $name] = FormFields::snapshot($fields, (array) $request->input('answers', []));
        $consent = ['given' => $consentRequired, 'text' => $form->setting('consent_text')];

        // A form with a "date & time slot" field books the call: the lead and the slot are saved together or not at all.
        $slotField = collect($fields)->firstWhere('type', 'slot');
        $slotRow = $slotField ? collect($rows)->firstWhere('key', $slotField['key']) : null;
        $booking = null;

        if ($slotField) {
            $start = $bookings->parse($slotRow['raw'] ?? null);
            try {
                $submission = DB::transaction(function () use ($form, $rows, $email, $name, $request, $consent, $responses, $bookings, $start, &$booking) {
                    if (!$start || !$bookings->claimable($start)) {
                        throw new \DomainException('slot unavailable');
                    }
                    $submission = $responses->store($form, $rows, $email, $name, $request, $consent);
                    $booking = $bookings->create($form, $submission, $start, $rows);
                    if (!$booking) {
                        throw new \DomainException('slot unavailable');
                    }

                    return $submission;
                });
            } catch (\DomainException|\Illuminate\Database\UniqueConstraintViolationException) {
                return $this->respond($request, 'error', 'That time has just been taken or is no longer available. Please choose another.', 422, [
                    'errors' => ['answers.' . $slotField['key'] => ['That time is no longer available. Please choose another.']],
                    'refresh_slots' => true,
                ]);
            } catch (\Throwable $e) {
                return $this->failed($request, $e, $form, $email);
            }
        } else {
            try {
                $submission = $responses->store($form, $rows, $email, $name, $request, $consent);
            } catch (\Throwable $e) {
                return $this->failed($request, $e, $form, $email);
            }
        }

        $redirect = (string) $form->setting('redirect_url');
        $redirect = str_starts_with($redirect, '/') && !str_starts_with($redirect, '//') ? $redirect : null;

        if ($redirect && !$request->expectsJson()) {
            return redirect($redirect)->with('form_success', $form->setting('success_message'));
        }

        return $this->respond($request, 'ok', $form->setting('success_message'), 200, [
            'redirect' => $redirect,
            'popup' => $responses->popup($form, $submission),
            'booking' => $booking ? ['when' => $bookings->label($booking->starts_at)] : null,
        ]);
    }

    /** Log the real cause for us; the visitor only sees a calm message, never SQL or stack details. */
    protected function failed(Request $request, \Throwable $e, Form $form, ?string $email)
    {
        report($e);
        \Illuminate\Support\Facades\Log::error('Form submission failed', [
            'form_id' => $form->id,
            'slug' => $form->slug,
            'error' => $e->getMessage(),
        ]);

        return $this->respond($request, 'error', 'Sorry, something went wrong on our side and your request was not saved. Please try again in a few minutes, or email us directly.', 500);
    }

    protected function respond(Request $request, string $status, ?string $message, int $code = 200, array $extra = [])
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => $status, 'message' => $message] + $extra, $status === 'ok' ? 200 : $code);
        }

        return back()->with($status === 'ok' ? 'form_success' : 'form_error', $message);
    }
}
