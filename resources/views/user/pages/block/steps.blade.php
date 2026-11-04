@php $d = $block->data ?? []; @endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb {{ ($d['tone'] ?? 'white') === 'tint' ? 'is-tint' : '' }}">
    <div class="container">
        @if (!empty($d['title']) || !empty($d['subtitle']))
            <div class="fe-pb-heading">
                @if (!empty($d['title']))<h2>{{ $d['title'] }}</h2>@endif
                @if (!empty($d['subtitle']))<p>{{ $d['subtitle'] }}</p>@endif
            </div>
        @endif
        <ol class="fe-pb-steps">
            @foreach (($d['items'] ?? []) as $item)
                <li>
                    <h3>{{ $item['title'] ?? '' }}</h3>
                    @if (!empty($item['text']))<p>{!! pb_text($item['text']) !!}</p>@endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
