@extends('admin.master_page')
@section('title', 'Bookings | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>Bookings</h1>
            <p class="fe-title-sub"><span class="fe-num">{{ $counts['pending'] }}</span> waiting for a decision, <span class="fe-num">{{ $counts['upcoming'] }}</span> upcoming. Times in {{ $zone }}.</p>
        </div>
        <a href="{{ route('admin.bookings.settings') }}" class="btn btn-outline-secondary">Availability &amp; calendar</a>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        @foreach (['pending' => 'Needs approval', 'upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.bookings.index', ['tab' => $key]) }}" class="btn btn-sm {{ $view === 'list' && $tab === $key ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}@if ($key === 'pending' && $counts['pending']) <span class="fe-num">({{ $counts['pending'] }})</span>@endif</a>
        @endforeach
        <a href="{{ route('admin.bookings.index', ['view' => 'calendar']) }}" class="btn btn-sm ms-auto {{ $view === 'calendar' ? 'btn-primary' : 'btn-outline-secondary' }}">Calendar</a>
    </div>

    @if ($view === 'calendar')
        @php
            $cursor = $month->startOfWeek(); $end = $month->endOfMonth()->endOfWeek(); $todayKey = now($tz)->format('Y-m-d');
        @endphp
        <div class="d-flex align-items-center gap-2 mb-2">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.bookings.index', ['view' => 'calendar', 'month' => $month->subMonth()->format('Y-m')]) }}" aria-label="Previous month">‹</a>
            <strong class="mx-2">{{ $month->format('F Y') }}</strong>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.bookings.index', ['view' => 'calendar', 'month' => $month->addMonth()->format('Y-m')]) }}" aria-label="Next month">›</a>
        </div>
        <style>
            .fe-cal{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));border:1px solid var(--bs-border-color,#dfe5e8);border-radius:12px;overflow:hidden;background:#fff}
            .fe-cal-h{padding:8px 10px;font-size:12px;font-weight:600;color:#67787a;background:#f7f9fb;border-bottom:1px solid #e6ecee}
            .fe-cal-d{min-height:104px;padding:6px;border-right:1px solid #eef2f3;border-bottom:1px solid #eef2f3;font-size:12px}
            .fe-cal-d.is-off{background:#fafbfb;color:#aab5b8}.fe-cal-d.is-today .fe-cal-n{background:#153f5d;color:#fff}
            .fe-cal-n{display:inline-grid;place-items:center;min-width:22px;height:22px;border-radius:11px;font-weight:600;margin-bottom:4px}
            .fe-cal-b{display:block;padding:3px 6px;margin-top:3px;border-radius:6px;text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;background:#e6f2f8;color:#153f5d}
            .fe-cal-b.is-pending{background:#fff3cf;color:#6b4a00}
            @media(max-width:700px){.fe-cal-d{min-height:64px}.fe-cal-b{font-size:10px;padding:2px 3px}}
        </style>
        <div class="fe-cal" role="grid" aria-label="Bookings in {{ $month->format('F Y') }}">
            @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d)<div class="fe-cal-h">{{ $d }}</div>@endforeach
            @while ($cursor <= $end)
                @php $key = $cursor->format('Y-m-d'); @endphp
                <div class="fe-cal-d {{ $cursor->month !== $month->month ? 'is-off' : '' }} {{ $key === $todayKey ? 'is-today' : '' }}">
                    <span class="fe-cal-n">{{ $cursor->day }}</span>
                    @foreach ($items[$key] ?? [] as $b)
                        <a class="fe-cal-b {{ $b->isPending() ? 'is-pending' : '' }}" href="{{ route('admin.bookings.show', $b) }}" title="{{ $b->name }} ({{ $b->status }})">{{ \Carbon\CarbonImmutable::instance($b->starts_at)->setTimezone($tz)->format('g:i') }} {{ $b->name ?: $b->email }}</a>
                    @endforeach
                </div>
                @php $cursor = $cursor->addDay(); @endphp
            @endwhile
        </div>
        <p class="fe-item-s mt-2">Blue is confirmed, amber is waiting for a decision.</p>
    @else
        <div class="card"><div class="card-body p-0">
            <table class="table align-middle mb-0" data-no-filter>
                <thead><tr><th scope="col">When</th><th scope="col">Contact</th><th scope="col">Organisation</th><th scope="col">Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($bookings as $b)
                    @php $org = collect($b->details)->firstWhere('key', 'company')['value'] ?? ''; @endphp
                    <tr>
                        <td class="fe-num text-nowrap"><a href="{{ route('admin.bookings.show', $b) }}">{{ $b->whenLabel() }}</a></td>
                        <td>{{ $b->name ?: 'No name' }}<div class="fe-item-s">{{ $b->email }}</div></td>
                        <td>{{ $org }}</td>
                        <td>
                            @switch($b->status)
                                @case('pending') <span class="badge bg-warning">Needs approval</span> @break
                                @case('approved') <span class="badge bg-success">Confirmed</span> @break
                                @case('declined') <span class="badge bg-secondary">Declined</span> @break
                                @default <span class="badge bg-secondary">Cancelled</span>
                            @endswitch
                        </td>
                        <td class="text-end"><a href="{{ route('admin.bookings.show', $b) }}" class="btn btn-sm btn-outline-secondary">{{ $b->isPending() ? 'Review' : 'Open' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 fe-item-s">Nothing here yet. New requests from the consultancy page appear in this list.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
        <div class="mt-3">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
