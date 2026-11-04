@php
    use App\Models\Event;
    $d = $block->data ?? [];
    $show = $d['show'] ?? 'upcoming';
    $limit = max(1, min(12, (int) ($d['limit'] ?? 3)));
    $query = Event::visibleOnSite()->with('category')
        ->when(!empty($d['category_id']), fn ($q) => $q->where('category_id', (int) $d['category_id']))
        ->when(empty($d['category_id']) && !empty($d['event_type']), fn ($q) => $q->where('type', $d['event_type']));
    if ($show === 'upcoming') {
        $query->notPast()->orderBy('start_date');
    } elseif ($show === 'past') {
        $query->past()->orderByDesc('start_date');
    } else {
        $query->orderByDesc('start_date');
    }
    $events = $query->take($limit)->get();
    $empty = $show === 'past' ? 'No past events to show yet.' : 'No events are scheduled right now. Check back soon.';
@endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb">
    <div class="container">
        @if (!empty($d['title']) || !empty($d['subtitle']))
            <div class="fe-pb-heading">
                @if (!empty($d['title']))<h2>{{ $d['title'] }}</h2>@endif
                @if (!empty($d['subtitle']))<p>{{ $d['subtitle'] }}</p>@endif
            </div>
        @endif
        @if ($events->isEmpty())
            <p class="fe-pb-muted">{{ $empty }}</p>
        @else
            <ul class="fe-pb-events">
                @foreach ($events as $event)
                    <li>
                        <a href="{{ route('events.show', $event->slug) }}">
                            <span class="fe-pb-edate">{{ $event->start_date?->format('j M Y') }}</span>
                            <span class="fe-pb-etitle">{{ $event->title }}<small>{{ $event->highlight_line ?: $event->location }}</small></span>
                            <span class="fe-pb-etype">{{ $event->category_label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        @if (!empty($d['link_text']) && !empty($d['link']))
            <p class="fe-pb-more"><a class="fe-pb-link" href="{{ $d['link'] }}">{{ $d['link_text'] }}</a></p>
        @endif
    </div>
</section>
