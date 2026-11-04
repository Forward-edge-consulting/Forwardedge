@php $d = $block->data ?? []; $tone = in_array(($d['tone'] ?? 'white'), ['white', 'tint', 'navy'], true) ? ($d['tone'] ?? 'white') : 'white'; @endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb is-{{ $tone }}">
    <div class="container">
        @if (!empty($d['title']) || !empty($d['subtitle']))
            <div class="fe-pb-heading">
                @if (!empty($d['title']))<h2>{{ $d['title'] }}</h2>@endif
                @if (!empty($d['subtitle']))<p>{{ $d['subtitle'] }}</p>@endif
            </div>
        @endif
        <dl class="fe-pb-stats">
            @foreach (($d['items'] ?? []) as $item)
                <div>
                    <dt>{{ $item['label'] ?? '' }}</dt>
                    <dd>{{ $item['value'] ?? '' }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
