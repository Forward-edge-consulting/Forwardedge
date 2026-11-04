@php $d = $block->data ?? []; @endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb {{ ($d['tone'] ?? 'white') === 'tint' ? 'is-tint' : '' }}">
    <div class="container">
        <div class="fe-pb-prose {{ ($d['width'] ?? 'narrow') === 'wide' ? 'is-wide' : '' }}">
            @if (!empty($d['title']))<h2>{{ $d['title'] }}</h2>@endif
            {!! \App\Support\RichText::cleanDocument($d['body'] ?? '') !!}
        </div>
    </div>
</section>
