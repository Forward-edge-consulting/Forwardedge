@props(['appearance' => 'always'])
@if (\App\Support\Turnstile::enabled())
    <div class="cf-turnstile fe-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-appearance="{{ $appearance }}" data-theme="auto"></div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
