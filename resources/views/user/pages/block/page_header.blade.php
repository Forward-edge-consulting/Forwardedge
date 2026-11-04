@php
    $d = $block->data ?? [];
    $image = !empty($d['image']) ? asset('storage/' . ltrim($d['image'], '/')) : null;
@endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb fe-pb-header">
    <div class="container {{ $image ? 'fe-pb-split' : '' }}">
        <div class="fe-pb-header-copy">
            @if (!empty($d['kicker']))<p class="fe-pb-kicker">{{ $d['kicker'] }}</p>@endif
            <h1>{!! pb_text($d['title'] ?? '') !!}</h1>
            @if (!empty($d['subtitle']))<p class="fe-pb-lead">{!! pb_text($d['subtitle']) !!}</p>@endif
            @if (!empty($d['primary_text']) || !empty($d['secondary_text']))
                <div class="fe-pb-actions">
                    @if (!empty($d['primary_text']) && !empty($d['primary_link']))
                        <a class="fe-pb-btn" href="{{ $d['primary_link'] }}">{{ $d['primary_text'] }}</a>
                    @endif
                    @if (!empty($d['secondary_text']) && !empty($d['secondary_link']))
                        <a class="fe-pb-link" href="{{ $d['secondary_link'] }}">{{ $d['secondary_text'] }}</a>
                    @endif
                </div>
            @endif
        </div>
        @if ($image)
            <figure class="fe-pb-media"><img src="{{ $image }}" alt="{{ $d['image_alt'] ?? '' }}" loading="eager"></figure>
        @endif
    </div>
</section>
