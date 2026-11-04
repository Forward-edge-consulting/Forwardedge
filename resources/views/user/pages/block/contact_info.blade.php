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
        <dl class="fe-pb-contact">
            @if (!empty($d['email']))<div><dt>Email</dt><dd><a href="mailto:{{ $d['email'] }}">{{ $d['email'] }}</a></dd></div>@endif
            @if (!empty($d['phone']))<div><dt>Phone</dt><dd><a href="tel:{{ preg_replace('/[^\d+]/', '', $d['phone']) }}">{{ $d['phone'] }}</a></dd></div>@endif
            @if (!empty($d['address']))<div><dt>Address</dt><dd>{!! pb_text($d['address']) !!}</dd></div>@endif
            @if (!empty($d['hours']))<div><dt>Hours</dt><dd>{!! pb_text($d['hours']) !!}</dd></div>@endif
        </dl>
    </div>
</section>
