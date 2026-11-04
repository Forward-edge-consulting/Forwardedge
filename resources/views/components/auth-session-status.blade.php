@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'fe-status', 'role' => 'status']) }}>
        {{ $status }}
    </div>
@endif
