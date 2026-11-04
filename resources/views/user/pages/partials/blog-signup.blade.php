@if ($form && $form->isOpen())
    <aside class="fe-blog-signup" aria-label="Subscribe">
        <h3>{{ $heading ?: 'Get new posts by email' }}</h3>
        @if ($form->description && !$form->is_system)<p>{{ $form->description }}</p>@endif
        @include('user.partials.form-embed', ['form' => $form])
    </aside>
@endif
