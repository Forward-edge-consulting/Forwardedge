@php
    $isContactPage = $contactPage ?? false;
    $mapQuery = rawurlencode(config('contact.address'));
@endphp
<section class="fe-contact-details tj-contact-section section-gap section-gap-x {{ $isContactPage ? 'fe-contact-page' : '' }}" aria-labelledby="contact-details-heading">
    <div class="container">
        <div class="fe-contact-grid">
            <div class="fe-contact-copy">
                <p class="fe-contact-eyebrow">Forward Edge Consulting</p>
                @if ($isContactPage)
                    <h1 id="contact-details-heading">Let’s talk about<br><span>your next step.</span></h1>
                @else
                    <h2 id="contact-details-heading">Get in <span>touch.</span></h2>
                @endif
                <p class="fe-contact-intro">Have a project in mind or a question for our team? Call, email, or visit us in Yaba.</p>
                <dl class="fe-contact-list">
                    <div><dt>Email us</dt><dd><a href="mailto:{{ config('contact.email') }}">{{ config('contact.email') }}</a></dd></div>
                    <div><dt>Call us</dt><dd><a href="tel:{{ config('contact.phone_link') }}">{{ config('contact.phone') }}</a></dd></div>
                    <div><dt>Visit our office</dt><dd><address>{{ config('contact.address') }}</address></dd></div>
                </dl>
            </div>
            <div class="fe-contact-location">
                <div class="fe-contact-map">
                    <iframe title="Map to Forward Edge Consulting, Yaba, Lagos" src="https://maps.google.com/maps?q={{ $mapQuery }}&amp;output=embed" width="640" height="480" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
                <a class="fe-contact-directions" href="https://www.google.com/maps/search/?api=1&amp;query={{ $mapQuery }}" target="_blank" rel="noopener noreferrer">Open in Google Maps <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </div>
</section>
@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('frontend/assets/css/contact.css') }}">
    @endpush
@endonce
