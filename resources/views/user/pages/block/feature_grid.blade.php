@php
    $d = $block->data ?? [];
    $tone = in_array(($d['tone'] ?? 'white'), ['white', 'tint', 'navy'], true) ? ($d['tone'] ?? 'white') : 'white';
    $cols = in_array((string) ($d['columns'] ?? '3'), ['2', '3', '4'], true) ? (string) ($d['columns'] ?? '3') : '3';
@endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb is-{{ $tone }}">
    <div class="container">
        @if (!empty($d['title']) || !empty($d['subtitle']))
            <div class="fe-pb-heading">
                @if (!empty($d['title']))<h2>{{ $d['title'] }}</h2>@endif
                @if (!empty($d['subtitle']))<p>{{ $d['subtitle'] }}</p>@endif
            </div>
        @endif
        <ul class="fe-pb-cards is-{{ $cols }}">
            @foreach (($d['items'] ?? []) as $item)
                <li>
                    <h3>{{ $item['title'] ?? '' }}</h3>
                    @if (!empty($item['text']))<p>{!! pb_text($item['text']) !!}</p>@endif
                    @if (!empty($item['link_text']) && !empty($item['link']))<a class="fe-pb-link" href="{{ $item['link'] }}">{{ $item['link_text'] }}</a>@endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
