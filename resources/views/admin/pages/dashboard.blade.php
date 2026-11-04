@extends('admin.master_page')
@section('title', 'Dashboard | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>{{ $greeting }}, {{ $firstName }}</h1>
            <p class="fe-title-sub">
                @if ($kpis['weekResponses'] > 0)
                    <span class="fe-num">{{ $kpis['weekResponses'] }}</span> new {{ \Illuminate\Support\Str::plural('response', $kpis['weekResponses']) }} this week.
                @else
                    No new responses this week.
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.blogs.store') }}" class="m-0">@csrf<button class="btn btn-outline-secondary">Write a post</button></form>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Create event</a>
        </div>
    </div>

    <div class="dz-kpis">
        <a class="dz-kpi is-yellow" href="{{ route('admin.responses.index', ['status' => 'new']) }}">
            <div class="dz-kpi-top"><span class="dz-chip"><i class="bi bi-inbox" aria-hidden="true"></i></span></div>
            <div><div class="dz-kpi-value">{{ number_format($kpis['leads']) }}</div><div class="dz-kpi-label">Contacts ready for Brevo</div></div>
        </a>
        <a class="dz-kpi is-blue" href="{{ route('admin.events.list', ['when' => 'upcoming']) }}">
            <div class="dz-kpi-top"><span class="dz-chip"><i class="bi bi-calendar-event" aria-hidden="true"></i></span></div>
            <div><div class="dz-kpi-value">{{ number_format($kpis['upcoming']) }}</div><div class="dz-kpi-label">Upcoming events</div></div>
        </a>
        <a class="dz-kpi is-navy" href="{{ route('admin.blogs.index') }}">
            <div class="dz-kpi-top"><span class="dz-chip"><i class="bi bi-journal-text" aria-hidden="true"></i></span></div>
            <div><div class="dz-kpi-value">{{ number_format($kpis['postsLive']) }}</div><div class="dz-kpi-label">Blog posts live</div><div class="dz-kpi-sub">{{ $kpis['postsDraft'] }} {{ \Illuminate\Support\Str::plural('draft', $kpis['postsDraft']) }}</div></div>
        </a>
        <a class="dz-kpi is-green" href="{{ route('pb.pages') }}">
            <div class="dz-kpi-top"><span class="dz-chip"><i class="bi bi-file-earmark-richtext" aria-hidden="true"></i></span></div>
            <div><div class="dz-kpi-value">{{ number_format($kpis['pagesLive']) }}</div><div class="dz-kpi-label">Pages live</div><div class="dz-kpi-sub">{{ $kpis['pagesDraft'] }} {{ \Illuminate\Support\Str::plural('draft', $kpis['pagesDraft']) }}</div></div>
        </a>
    </div>

    <div class="dz-grid">
        <section class="fe-panel" aria-labelledby="attn-title">
            <div class="dz-head"><h2 id="attn-title">Needs attention</h2></div>
            @if ($attention->isEmpty())
                <p class="dz-clear"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> All caught up. Nothing is waiting on you.</p>
            @else
                <ul class="dz-todo">
                    @foreach ($attention as $a)
                        <li>
                            <span class="dz-chip is-{{ $a['tone'] }}"><i class="bi {{ $a['icon'] }}" aria-hidden="true"></i></span>
                            <div class="dz-todo-t"><strong><span class="fe-num">{{ number_format($a['n']) }}</span> {{ $a['title'] }}</strong><span>{{ $a['sub'] }}</span></div>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ $a['url'] }}">{{ $a['cta'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="dz-feature" aria-labelledby="next-title">
            <span class="fe-eyebrow" id="next-title">Next event</span>
            @if ($nextEvent)
                @php
                    $page = $nextEvent->pages->sortByDesc('id')->first();
                    $days = (int) now()->startOfDay()->diffInDays($nextEvent->start_date->copy()->startOfDay(), false);
                @endphp
                <h2><a href="{{ route('admin.events.dashboard', $nextEvent) }}">{{ $nextEvent->title }}</a></h2>
                <p class="dz-feature-meta">{{ $nextEvent->start_date->format('D j M Y, g:ia') }}@if ($nextEvent->location), {{ $nextEvent->location }}@endif</p>
                <div class="dz-days"><strong>{{ $days <= 0 ? 'Today' : $days }}</strong>@if ($days > 0)<span>{{ \Illuminate\Support\Str::plural('day', $days) }} to go</span>@endif</div>
                <div class="dz-feature-status">
                    <span class="dz-pill">{{ $nextEvent->category_label }}</span>
                    <span class="dz-pill {{ $page && $page->status === 'published' ? 'is-ok' : 'is-warn' }}">{{ $page ? ($page->status === 'published' ? 'Page live' : 'Page is a draft') : 'No page yet' }}</span>
                    <span class="dz-pill">Button: {{ ['none' => 'none', 'interest' => 'interest form', 'link' => 'external link', 'contact' => 'contact us'][$nextEvent->cta_type] ?? 'none' }}</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if ($page)
                        <a class="btn btn-primary" href="{{ route('pb.blocks', $page) }}">Edit the page</a>
                    @else
                        <a class="btn btn-primary" href="{{ route('admin.events.dashboard', $nextEvent) }}#page">Build the page</a>
                    @endif
                    <a class="btn btn-outline-secondary" href="{{ route('admin.events.dashboard', $nextEvent) }}">Event details</a>
                </div>
            @else
                <h2>Nothing scheduled</h2>
                <p class="dz-feature-meta">Published events with a future date appear here, with a countdown.</p>
                <a class="btn btn-primary" href="{{ route('admin.events.create') }}">Create an event</a>
            @endif
        </section>
    </div>

    <div class="dz-grid">
        <section class="fe-panel" aria-labelledby="recent-title">
            <div class="dz-head"><h2 id="recent-title">Recent responses</h2>@if ($recentResponses->isNotEmpty())<a class="fe-link" href="{{ route('admin.responses.index') }}">See all</a>@endif</div>
            @forelse ($recentResponses as $r)
                @php $who = $r->name ?: ($r->email ?: 'Response'); @endphp
                <ul class="dz-feed" style="margin:0"><li>
                    <span class="fe-av c{{ abs(crc32((string) $r->email)) % 4 }}" aria-hidden="true">{{ strtoupper(mb_substr($who, 0, 1)) }}</span>
                    <div class="dz-feed-t"><a href="{{ route('admin.responses.show', $r) }}">{{ $who }}</a><span>{{ $r->form->name ?? 'Form' }}{{ $r->source_label ? ', ' . $r->source_label : '' }}</span></div>
                    <time datetime="{{ $r->created_at->toIso8601String() }}">{{ $r->created_at->diffForHumans(short: true) }}</time>
                </li></ul>
            @empty
                <p class="fe-empty">No responses yet. Sign-ups and form answers appear here.</p>
            @endforelse
        </section>

        <section class="fe-panel" aria-labelledby="up-title">
            <div class="dz-head"><h2 id="up-title">Coming up</h2><a class="fe-link" href="{{ route('admin.events.list') }}">All events</a></div>
            @if ($comingUp->isEmpty())
                <p class="fe-empty">No other upcoming events.</p>
            @else
                <ul class="dz-upnext">
                    @foreach ($comingUp as $event)
                        <li>
                            <div class="dz-date"><b>{{ $event->start_date->format('j') }}</b><small>{{ $event->start_date->format('M') }}</small></div>
                            <div class="dz-feed-t"><a href="{{ route('admin.events.dashboard', $event) }}">{{ $event->title }}</a><span>{{ $event->location }}</span></div>
                            <span class="badge bg-primary">{{ $event->category_label }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
@endsection
