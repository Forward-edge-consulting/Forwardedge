@php
    $d = $block->data ?? [];
    $event = ($block->page ?? null)?->pageable;
    $event = $event instanceof \App\Models\Event ? $event : null;
@endphp
@include('user.pages.block.partials.assets')
@if ($event)
    @php
        $over = $event->is_past;
        $when = $event->start_date?->format('l j F Y');
        if ($event->end_date && !$event->end_date->isSameDay($event->start_date)) {
            $when = $event->start_date->format('j M') . ' to ' . $event->end_date->format('j M Y');
        }
        $where = trim(($event->venue ? $event->venue . ', ' : '') . ($event->location ?? ''), ', ');
    @endphp
    <section class="fe-pb is-tint">
        <div class="container">
            @if (!empty($d['title']))<div class="fe-pb-heading"><h2>{{ $d['title'] }}</h2></div>@endif
            <dl class="fe-pb-facts">
                <div><dt>When</dt><dd>{{ $when }}@if ($event->start_date && !$event->start_date->isMidnight())<small>{{ $event->start_date->format('g:ia') }}</small>@endif</dd></div>
                @if ($where)<div><dt>Where</dt><dd>{{ $where }}</dd></div>@endif
                <div><dt>Type</dt><dd>{{ $event->category_label }}</dd></div>
                <div><dt>Status</dt><dd>{{ $over ? 'Completed' : 'Upcoming' }}</dd></div>
            </dl>
            @if (!empty($d['note']))<p class="fe-pb-muted">{{ $d['note'] }}</p>@endif
            @if (!$over && !empty($d['button_text']) && !empty($d['button_link']))
                <p class="fe-pb-more"><a class="fe-pb-btn" href="{{ $d['button_link'] }}">{{ $d['button_text'] }}</a></p>
            @endif
        </div>
    </section>
@elseif (!empty($pbEdit))
    <section class="fe-pb"><div class="container"><p class="fe-pb-note">Event details appear here once this page is linked to an event (Page settings, Bind to Event).</p></div></section>
@endif
