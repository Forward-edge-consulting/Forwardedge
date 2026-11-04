@extends('user.master_page')
@section('body_attributes', 'data-native-scroll')
@section('title', 'Events and Programmes | Forward Edge Consulting')
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/pages.css') }}?v=3">
@endpush

@section('main')
<section class="fe-pb fe-pb-header fe-ev-head">
    <div class="container">
        <p class="fe-pb-kicker">Events and programmes</p>
        <h1>Where we teach, convene and show up</h1>
        <p class="fe-pb-lead">Bootcamps, workshops and programmes from Forward Edge, and a record of the ones we have already run.</p>

        <nav class="fe-ev-tabs" aria-label="Upcoming or past">
            <a href="{{ route('events.index', array_filter(['when' => 'upcoming', 'category' => $active?->slug])) }}" @class(['is-current' => $when === 'upcoming']) @if ($when === 'upcoming') aria-current="page" @endif>Upcoming <span class="fe-num">{{ $upcomingCount }}</span></a>
            <a href="{{ route('events.index', array_filter(['when' => 'past', 'category' => $active?->slug])) }}" @class(['is-current' => $when === 'past']) @if ($when === 'past') aria-current="page" @endif>Past <span class="fe-num">{{ $pastCount }}</span></a>
        </nav>

        @if ($categories->count() > 1 || $active)
            <div class="fe-ev-chips" role="group" aria-label="Category">
                <a href="{{ route('events.index', ['when' => $when]) }}" @class(['is-current' => !$active])>All</a>
                @foreach ($categories as $c)
                    <a href="{{ route('events.index', ['when' => $when, 'category' => $c->slug]) }}" @class(['is-current' => $active?->id === $c->id])>{{ $c->name }} <span class="fe-num">{{ $c->n }}</span></a>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="fe-pb" style="padding-top:24px">
    <div class="container">
        @if ($events->isEmpty())
            <p class="fe-pb-muted fe-ev-empty">
                @if ($when === 'upcoming')
                    Nothing is scheduled right now. @if ($pastCount > 0) See <a class="fe-pb-link" href="{{ route('events.index', ['when' => 'past']) }}">what we have already run</a>, or @endif <a class="fe-pb-link" href="{{ route('contact') }}">get in touch</a> and we will tell you about the next one.
                @else
                    Nothing here yet.
                @endif
            </p>
        @elseif ($when === 'upcoming')
            <ul class="fe-pb-events">
                @foreach ($events as $event)
                    <li>
                        <a href="{{ route('events.show', $event->slug) }}">
                            <span class="fe-pb-edate">{{ $event->start_date?->format('j M Y') }}</span>
                            <span class="fe-pb-etitle">{{ $event->title }}<small>{{ $event->location }}</small></span>
                            <span class="fe-pb-etype">{{ $event->category_label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="fe-ev-wall">
                @foreach ($events as $event)
                    <article class="fe-ev-card">
                        <a href="{{ route('events.show', $event->slug) }}">
                            @if ($event->thumbnail)
                                <figure><img src="{{ asset('storage/' . $event->thumbnail) }}" alt="" loading="lazy"></figure>
                            @endif
                            <div class="fe-ev-card-body">
                                <span class="fe-pb-etype">{{ $event->category_label }}</span>
                                <h2>{{ $event->title }}</h2>
                                <p class="fe-ev-meta">{{ $event->start_date?->format('j M Y') }}@if ($event->location), {{ $event->location }}@endif</p>
                                @if ($event->highlight_line)<p class="fe-ev-high">{{ $event->highlight_line }}</p>@endif
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="tj-pagination d-flex justify-content-center mt-4">{{ $events->links('vendor.pagination.custom') }}</div>
    </div>
</section>
@endsection
