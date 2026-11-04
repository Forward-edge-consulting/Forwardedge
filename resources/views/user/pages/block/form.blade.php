@php
    $d = $block->data ?? [];
    $form = !empty($d['form_id']) ? \App\Models\Form::find($d['form_id']) : null;
    $tone = in_array(($d['tone'] ?? 'white'), ['white', 'tint', 'navy'], true) ? ($d['tone'] ?? 'white') : 'white';
@endphp
@include('user.pages.block.partials.assets')
@if ($form)
    @php $anchor = $d['anchor'] ?? ($form->slug === 'interest' ? 'interest' : null); @endphp
    <section class="fe-pb is-{{ $tone }}" @if ($anchor) id="{{ $anchor }}" @endif>
        <div class="container">
            <div class="fe-pb-formwrap">
                <h2>{{ $d['title'] ?? $form->name }}</h2>
                @php $intro = $d['intro'] ?? (!$form->is_system ? $form->description : null); @endphp
                @if ($intro)<p>{{ $intro }}</p>@endif
                @include('user.partials.form-embed', ['form' => $form])
            </div>
        </div>
    </section>
@elseif (!empty($pbEdit))
    <section class="fe-pb"><div class="container"><p class="fe-pb-note">This section shows a form, but the form was deleted. Pick another in the editor.</p></div></section>
@endif
