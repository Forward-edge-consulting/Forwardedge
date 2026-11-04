@php $d = $block->data ?? []; @endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb {{ ($d['tone'] ?? 'white') === 'tint' ? 'is-tint' : '' }}">
    <div class="container">
        @if (!empty($d['title']))
            <div class="fe-pb-heading"><h2>{{ $d['title'] }}</h2></div>
        @endif
        <div class="fe-pb-quotes">
            @foreach (($d['items'] ?? []) as $item)
                <figure>
                    <blockquote><p>{!! pb_text($item['quote'] ?? '') !!}</p></blockquote>
                    <figcaption>
                        <strong>{{ $item['name'] ?? '' }}</strong>
                        @if (!empty($item['role']))<span>{{ $item['role'] }}</span>@endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
