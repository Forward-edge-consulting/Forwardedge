<button {{ $attributes->merge(['type' => 'submit', 'class' => 'fe-btn-primary']) }}>
    {{ $slot }}
</button>
