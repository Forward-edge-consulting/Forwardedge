<div class="banner-img fe-hero-photo fe-hero-slideshow" data-hero-slideshow role="region" aria-roledescription="carousel" aria-label="Forward Edge services">
    <img class="fe-hero-slide is-active" fetchpriority="high" src="{{ asset('frontend/assets/images/hero/consulting-team.jpg') }}" alt="Professionals meeting inside a glass-walled office" data-caption="Consulting & collaboration" style="object-position:28% center">
    <img class="fe-hero-slide" src="{{ asset('frontend/assets/images/hero/bootcamp-community.jpg') }}" alt="Forward Edge cybersecurity bootcamp participants smiling together for a group photograph" aria-hidden="true" data-caption="Our bootcamp community" decoding="async" style="object-position:50% center">
    <img class="fe-hero-slide" src="{{ asset('frontend/assets/images/hero/security-monitoring.jpg') }}" alt="A Black professional reviewing live data across two monitors" aria-hidden="true" data-caption="Security operations" decoding="async" style="object-position:40% center">
    <div class="fe-hero-slide-footer">
        <span data-slide-caption>Consulting & collaboration</span>
        <div class="fe-hero-controls" role="group" aria-label="Slideshow controls" hidden>
            <button type="button" data-slide-prev aria-label="Previous image">←</button>
            <button type="button" data-slide-toggle aria-label="Pause slideshow">Pause</button>
            <button type="button" data-slide-next aria-label="Next image">→</button>
        </div>
    </div>
    <span class="visually-hidden" data-slide-status aria-live="polite"></span>
</div>
@push('styles')
<style>
    .fe-hero-slideshow { min-height:390px; }
    .fe-hero-slideshow .fe-hero-slide { position:absolute; inset:0; opacity:0; transition:opacity 700ms cubic-bezier(.23,1,.32,1); }
    .fe-hero-slideshow .fe-hero-slide.is-active { opacity:1; }
    .fe-hero-slideshow.is-instant .fe-hero-slide { transition:none; }
    .fe-hero-slide-footer { position:absolute; inset:auto 0 0; z-index:2; display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; padding:20px; background:rgba(9,40,61,.88); color:white; font-size:14px; }
    .fe-hero-controls { display:flex; gap:6px; }
    .fe-hero-controls[hidden] { display:none; }
    .fe-hero-controls button { min-height:44px; min-width:44px; padding:8px 12px; border:1px solid rgba(255,255,255,.5); border-radius:6px; background:transparent; color:white; font:inherit; }
    .fe-hero-controls button:focus-visible { outline:2px solid white; outline-offset:3px; }
    @media (hover:hover) { .fe-hero-controls button:hover { background:rgba(255,255,255,.18); } }
    @media (prefers-reduced-motion:reduce) { .fe-hero-slideshow .fe-hero-slide { transition:none; } }
</style>
@endpush
@push('scripts')
<script src="{{ asset('frontend/assets/js/hero-slideshow.js') . '?v=20261028-2' }}" defer></script>
@endpush
