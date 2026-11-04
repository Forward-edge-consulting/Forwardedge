<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminResponseController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filtered($request)->with('form:id,name')->latest();
        $responses = $query->paginate(25)->withQueryString();

        $scope = FormSubmission::query()->when($request->integer('form'), fn ($q, $id) => $q->where('form_id', $id));
        $stats = [
            'ready' => (clone $scope)->readyToExport()->count(),
            'no_consent' => (clone $scope)->whereNotNull('email')->where('consent_given', false)->whereNull('exported_at')->count(),
            'no_email' => (clone $scope)->whereNull('email')->count(),
            'total' => (clone $scope)->count(),
        ];

        $lastBatch = FormSubmission::query()
            ->whereNotNull('export_batch')
            ->selectRaw('export_batch, count(*) as n, max(exported_at) as at')
            ->groupBy('export_batch')
            ->orderByDesc('at')
            ->first();

        return view('admin.pages.responses.index', [
            'responses' => $responses,
            'forms' => Form::orderBy('name')->get(['id', 'name']),
            'stats' => $stats,
            'lastBatch' => $lastBatch,
        ]);
    }

    public function show(FormSubmission $response)
    {
        $response->load('form');
        $sameEmail = $response->email ? FormSubmission::where('email', $response->email)->count() : 0;

        return view('admin.pages.responses.show', ['response' => $response, 'sameEmail' => $sameEmail]);
    }

    public function resendResource(FormSubmission $response, \App\Services\FormResponses $responses)
    {
        $response->load('form');
        abort_unless($response->form && $response->email, 404);

        return $responses->deliverResource($response->form, $response)
            ? back()->with('success', 'Resource emailed to ' . $response->email . '.')
            : back()->with('error', 'The email could not be sent. Check the mail settings, then try again.');
    }

    public function destroy(FormSubmission $response)
    {
        $response->delete();

        return redirect()->route('admin.responses.index')->with('success', 'Response deleted.');
    }

    /** Remove everything held about one person, across all forms. */
    public function forget(Request $request)
    {
        $email = strtolower((string) $request->validate(['email' => 'required|email'])['email']);
        $count = FormSubmission::where('email', $email)->delete();

        return redirect()->route('admin.responses.index')->with('success', "Deleted {$count} " . Str::plural('response', $count) . " for {$email}.");
    }

    /**
     * CSV for a manual Brevo import. Only contacts who agreed to be emailed are included.
     * scope=new marks them exported; scope=all re-downloads everyone consented without marking.
     */
    public function export(Request $request): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $scope = $request->input('scope') === 'all' ? 'all' : 'new';

        $query = FormSubmission::query()
            ->with('form:id,name')
            ->whereNotNull('email')
            ->where('consent_given', true)
            ->when($request->integer('form'), fn ($q, $id) => $q->where('form_id', $id))
            ->when($scope === 'new', fn ($q) => $q->whereNull('exported_at'))
            ->orderBy('created_at');

        $submissions = $query->get();
        if ($submissions->isEmpty()) {
            return back()->with('error', 'There are no new contacts to export.');
        }

        [$columns, $rows] = $this->buildRows($submissions);

        if ($scope === 'new') {
            FormSubmission::whereIn('id', $submissions->pluck('id'))->update([
                'exported_at' => now(),
                'export_batch' => (string) Str::uuid(),
            ]);
        }

        $filename = 'forward-edge-contacts-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($c) => $this->safe($row[$c] ?? ''), $columns));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function undoExport(string $batch)
    {
        $n = FormSubmission::where('export_batch', $batch)->update(['exported_at' => null, 'export_batch' => null]);

        return back()->with('success', "Marked {$n} " . Str::plural('contact', $n) . ' as not exported.');
    }

    protected function filtered(Request $request): Builder
    {
        return FormSubmission::query()
            ->when($request->integer('form'), fn ($q, $id) => $q->where('form_id', $id))
            ->when($request->input('status') === 'new', fn ($q) => $q->whereNull('exported_at'))
            ->when($request->input('status') === 'exported', fn ($q) => $q->whereNotNull('exported_at'))
            ->when($request->input('status') === 'no_consent', fn ($q) => $q->where('consent_given', false))
            ->when(filled($request->input('q')), function ($q) use ($request) {
                $term = '%' . trim($request->input('q')) . '%';
                $q->where(fn ($w) => $w->where('email', 'like', $term)->orWhere('name', 'like', $term));
            })
            ->when($request->date('from'), fn ($q, $d) => $q->where('created_at', '>=', $d->startOfDay()))
            ->when($request->date('to'), fn ($q, $d) => $q->where('created_at', '<=', $d->endOfDay()));
    }

    /** One row per email; later answers win. Brevo default attributes come first. */
    protected function buildRows($submissions): array
    {
        $people = [];
        $extra = [];

        foreach ($submissions as $s) {
            $p = $people[$s->email] ?? ['EMAIL' => $s->email, 'FIRSTNAME' => '', 'LASTNAME' => '', 'SMS' => ''];

            foreach ($s->data as $item) {
                $value = is_array($item['value']) ? implode('; ', $item['value']) : (string) $item['value'];
                $maps = $item['maps_to'] ?? '';
                if ($maps === 'FULLNAME') {
                    $parts = preg_split('/\s+/', trim($value), 2);
                    $p['FIRSTNAME'] = $parts[0] ?? '';
                    $p['LASTNAME'] = $parts[1] ?? $p['LASTNAME'];
                } elseif (in_array($maps, ['FIRSTNAME', 'LASTNAME', 'SMS'], true)) {
                    $p[$maps] = $value;
                } elseif (($item['type'] ?? '') !== 'email') {
                    $label = trim((string) $item['label']);
                    $extra[$label] = true;
                    $p[$label] = $value;
                }
            }

            $p['SOURCE'] = $s->source_label ?? '';
            $p['FORM'] = $s->form->name ?? '';
            $p['SUBMITTED_AT'] = $s->created_at->toDateTimeString();
            $p['CONSENT_AT'] = $s->consented_at?->toDateTimeString() ?? '';
            $people[$s->email] = $p;
        }

        $columns = array_merge(['EMAIL', 'FIRSTNAME', 'LASTNAME', 'SMS'], array_keys($extra), ['SOURCE', 'FORM', 'SUBMITTED_AT', 'CONSENT_AT']);

        return [$columns, array_values($people)];
    }

    /** Stop spreadsheet formula injection from user-supplied text. */
    protected function safe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
