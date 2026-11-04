@props(['value'])

<label {{ $attributes->merge(['class' => 'fe-label']) }}>
    {{ $value ?? $slot }}
</label>
