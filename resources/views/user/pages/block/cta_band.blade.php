@php $d = $block->data ?? []; @endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb is-navy">
    <div class="container">
        <h2>{!! pb_text($d['title'] ?? '') !!}</h2>
        @if (!empty($d['subtitle']))<p>{!! pb_text($d['subtitle']) !!}</p>@endif
        <div class="fe-pb-actions">
            @if (!empty($d['primary_text']) && !empty($d['primary_link']))<a class="fe-pb-btn" href="{{ $d['primary_link'] }}">{{ $d['primary_text'] }}</a>@endif
            @if (!empty($d['secondary_text']) && !empty($d['secondary_link']))<a class="fe-pb-link" href="{{ $d['secondary_link'] }}">{{ $d['secondary_text'] }}</a>@endif
        </div>
    </div>
</section>
