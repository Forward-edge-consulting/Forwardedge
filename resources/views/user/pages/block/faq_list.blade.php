@php $d = $block->data ?? []; @endphp
@include('user.pages.block.partials.assets')
<section class="fe-pb {{ ($d['tone'] ?? 'white') === 'tint' ? 'is-tint' : '' }}">
    <div class="container">
        <div class="fe-pb-split fe-pb-faq-wrap">
            <div class="fe-pb-heading">
                @if (!empty($d['title']))<h2>{{ $d['title'] }}</h2>@endif
                @if (!empty($d['subtitle']))<p>{{ $d['subtitle'] }}</p>@endif
            </div>
            <div class="fe-pb-faq">
                @foreach (($d['items'] ?? []) as $item)
                    <details>
                        <summary>{{ $item['q'] ?? '' }}</summary>
                        <p>{!! pb_text($item['a'] ?? '') !!}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</section>
