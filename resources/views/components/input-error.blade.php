@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'fe-error']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
