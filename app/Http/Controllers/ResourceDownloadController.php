<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Storage;

/** Serves the resource attached to a form to the person who submitted it. The link is a random token, valid for 30 days. */
class ResourceDownloadController extends Controller
{
    public const VALID_DAYS = 30;

    public function __invoke(string $token)
    {
        $submission = FormSubmission::with('form')->where('download_token', $token)->first();
        abort_unless($submission && $submission->form && $submission->form->hasResource(), 404);
        abort_if($submission->created_at->lt(now()->subDays(self::VALID_DAYS)), 410, 'This download link has expired. Please contact us and we will send a new one.');

        $form = $submission->form;
        $submission->forceFill([
            'resource_downloaded_at' => $submission->resource_downloaded_at ?? now(),
            'download_count' => $submission->download_count + 1,
        ])->save();

        if ($form->setting('resource_kind') === 'link') {
            return redirect()->away($form->setting('resource_url'))->header('X-Robots-Tag', 'noindex');
        }

        $path = (string) $form->setting('resource_path');
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download($path, $form->setting('resource_filename') ?: basename($path), ['X-Robots-Tag' => 'noindex']);
    }
}
