@extends('admin.master_page')
@section('title', 'Responses | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>Responses</h1>
            <p class="fe-title-sub">
                <span class="fe-num">{{ number_format($stats['ready']) }}</span> ready for Brevo.
                @if ($stats['no_consent'] > 0)
                    <span class="fe-num">{{ number_format($stats['no_consent']) }}</span> without recorded consent are held back.
                @endif
            </p>
        </div>
        <form method="POST" action="{{ route('admin.responses.export') }}">
            @csrf
            <input type="hidden" name="scope" value="new">
            <input type="hidden" name="form" value="{{ request('form') }}">
            <button class="btn btn-primary" @disabled($stats['ready'] === 0)>Export new contacts</button>
        </form>
    </div>

    @if ($lastBatch)
        <div class="fe-note">
            Last export: <span class="fe-num">{{ $lastBatch->n }}</span> {{ Str::plural('contact', $lastBatch->n) }} on {{ \Illuminate\Support\Carbon::parse($lastBatch->at)->format('j M Y, g:ia') }}.
            <form method="POST" action="{{ route('admin.responses.undo', $lastBatch->export_batch) }}" class="d-inline"
                  data-confirm="These {{ $lastBatch->n }} contacts will show as new again and be included in your next export."
                  data-confirm-title="Mark this export as not done?" data-confirm-action="Mark as not exported" data-confirm-danger="false">
                @csrf
                <button class="btn btn-link btn-sm p-0 align-baseline">Undo</button>
            </form>
        </div>
    @endif

    <form method="GET" class="fe-filters" role="search">
        <div>
            <label for="f-form" class="form-label">Form</label>
            <select id="f-form" name="form" class="form-select form-select-sm">
                <option value="">All forms</option>
                @foreach ($forms as $form)
                    <option value="{{ $form->id }}" @selected((int) request('form') === $form->id)>{{ $form->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-status" class="form-label">Status</label>
            <select id="f-status" name="status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="new" @selected(request('status') === 'new')>Not exported</option>
                <option value="exported" @selected(request('status') === 'exported')>Exported</option>
                <option value="no_consent" @selected(request('status') === 'no_consent')>No consent</option>
            </select>
        </div>
        <div class="fe-filters-grow">
            <label for="f-q" class="form-label">Search</label>
            <input id="f-q" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Name or email">
        </div>
        <div>
            <label for="f-from" class="form-label">From</label>
            <input id="f-from" type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div>
            <label for="f-to" class="form-label">To</label>
            <input id="f-to" type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
        </div>
        <div class="fe-filters-actions">
            <button class="btn btn-sm btn-outline-secondary">Apply</button>
            @if (request()->hasAny(['form', 'status', 'q', 'from', 'to']))
                <a href="{{ route('admin.responses.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <table class="table align-middle mb-0" data-no-filter>
                <thead>
                    <tr>
                        <th scope="col">Contact</th>
                        <th scope="col">Form</th>
                        <th scope="col">From</th>
                        <th scope="col">Received</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($responses as $r)
                        <tr>
                            <td>
                                @php $who = $r->name ?: ($r->email ?: '?'); @endphp
                                <div class="fe-who">
                                    <span class="fe-av c{{ abs(crc32((string) $r->email)) % 4 }}" aria-hidden="true">{{ strtoupper(mb_substr($who, 0, 1)) }}</span>
                                    <div class="fe-who-t">
                                        <a href="{{ route('admin.responses.show', $r) }}">{{ $r->name ?: ($r->email ?: 'No name') }}</a>
                                        <div class="fe-item-s">{{ $r->name && $r->email ? $r->email : ($r->email ? '' : 'No email given') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $r->form->name ?? 'Deleted form' }}</td>
                            <td class="fe-trunc">{{ $r->source_label ?: 'Unknown' }}</td>
                            <td class="fe-num text-nowrap">{{ $r->created_at->format('j M Y, g:ia') }}</td>
                            <td class="text-nowrap">
                                @if ($r->exported_at)
                                    <span class="badge bg-secondary">Exported</span>
                                @else
                                    <span class="badge bg-primary">New</span>
                                @endif
                                @if (!$r->consent_given)
                                    <span class="badge bg-warning">No consent</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5">
                                <p class="fe-empty mb-0">
                                    @if (request()->hasAny(['form', 'status', 'q', 'from', 'to']))
                                        No responses match these filters.
                                    @else
                                        No responses yet. Share a form link, or place a form on a page, and responses appear here.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
        <div class="fe-item-s">
            <span class="fe-num">{{ number_format($responses->total()) }}</span> {{ Str::plural('response', $responses->total()) }}
            @if ($stats['no_email'] > 0) · <span class="fe-num">{{ $stats['no_email'] }}</span> without an email address @endif
        </div>
        <div class="d-flex align-items-center gap-3">
            <form method="POST" action="{{ route('admin.responses.export') }}">
                @csrf
                <input type="hidden" name="scope" value="all">
                <input type="hidden" name="form" value="{{ request('form') }}">
                <button class="btn btn-link btn-sm p-0">Download everyone who consented</button>
            </form>
            {{ $responses->links() }}
        </div>
    </div>

    <details class="fe-help">
        <summary>How the Brevo import works</summary>
        <ol>
            <li>Press <strong>Export new contacts</strong>. You get a CSV with the columns EMAIL, FIRSTNAME, LASTNAME and SMS, then any other answers, then where each person came from.</li>
            <li>In Brevo, go to Contacts, choose Import contacts, upload the file and map any extra columns to attributes (or skip them).</li>
            <li>Only people who ticked the consent box are included. Those exported are marked so they are not exported twice. If an import fails, use <strong>Undo</strong> above.</li>
        </ol>
    </details>
</div>
@endsection
