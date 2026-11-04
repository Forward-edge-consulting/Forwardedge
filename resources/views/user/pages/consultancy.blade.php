@extends('user.master_page')
@section('body_attributes', 'data-native-scroll')
@section('title', 'Information Security Consulting | Forward Edge Consulting')
@php
    $callForm = \App\Models\Form::where('slug', config('consultancy.form_slug'))->where('is_system', false)->first();
    $hasForm = $callForm && $callForm->isOpen();
    $mailto = 'mailto:' . config('contact.email') . '?subject=' . rawurlencode('Security discovery call request');
    $videoId = config('consultancy.video_id');
    $photo = config('consultancy.photo');
    $consultant = config('consultancy.consultant');

    // One icon set (outline, 24px). Paths only; drawn by the i() helper below.
    $icons = [
        'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/>',
        'talk' => '<path d="M5 6h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-8l-4 3v-3H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1z"/>',
        'route' => '<circle cx="6" cy="18" r="2"/><circle cx="18" cy="6" r="2"/><path d="M8 18h6a3 3 0 0 0 0-6h-4a3 3 0 0 1 0-6h6"/>',
        'shield' => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.500-7-10V6z"/><path d="M9 12l2 2 4-4"/>',
        'database' => '<ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v6c0 1.700 3 3 7 3s7-1.300 7-3V6M5 12v6c0 1.700 3 3 7 3s7-1.300 7-3v-6"/>',
        'policy' => '<path d="M7 3h8l4 4v14H7z"/><path d="M15 3v4h4M10 12h6M10 16h6"/>',
        'people' => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.500 2.500-6 6-6s6 2.500 6 6"/><path d="M16 5.200a3 3 0 0 1 0 5.600M18 14.300c2 .8 3 2.700 3 5.700"/>',
        'incident' => '<path d="M12 4l9 16H3z"/><path d="M12 10v4M12 17v.010"/>',
        'compass' => '<circle cx="12" cy="12" r="9"/><path d="M15.500 8.500l-2 5-5 2 2-5z"/>',
        'lock' => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
    ];
    $i = fn (string $name) => new \Illuminate\Support\HtmlString('<svg class="fe-c-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>');

    $steps = [
        ['calendar', 'Choose a convenient time', 'Pick an available appointment and tell us a little about your organisation. Your first discovery call is free.'],
        ['talk', 'Talk through your concerns', 'We discuss your operations, existing safeguards and possible areas of exposure. This is an initial conversation, not a full audit.'],
        ['route', 'Get a clear way forward', 'We explain possible next steps. Where needed, Forward Edge can propose a separately scoped, paid security assessment or consulting support.'],
    ];
    $questions = [
        'Who can access sensitive business information—and who shouldn\'t?',
        'Could staff recognise an attempt to trick them into sharing information or sending money?',
        'Would your business know what to do if important systems or records became unavailable?',
        'Are your digital and physical safeguards keeping pace with growth?',
    ];
    $services = [
        ['shield', 'Information security assessments', 'Understand weaknesses in agreed systems, processes and information handling, with prioritised recommendations.'],
        ['database', 'Data protection & handling', 'Review how sensitive business and customer information is accessed, shared, stored and protected.'],
        ['policy', 'Policies & governance', 'Develop practical rules, responsibilities and oversight to support safer everyday decisions.'],
        ['people', 'Employee security readiness', 'Identify people-related risks and improve awareness, behaviours and reporting practices.'],
        ['incident', 'Incident preparedness', 'Prepare teams to respond more effectively when security problems disrupt business operations.'],
        ['compass', 'Security strategy & advisory', 'Make informed security decisions aligned with your organisation\'s size, priorities and resources.'],
    ];
    $scope = [
        ['Free discovery call:', 'initial discussion, concerns and possible next steps.'],
        ['Paid assessment:', 'agreed scope, evidence review, documented findings and recommendations.'],
        ['Further support:', 'separately agreed consulting or implementation work where needed.'],
    ];
@endphp
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/consultancy.css') }}?v=14">
<link rel="stylesheet" href="{{ asset('frontend/assets/css/forms.css') }}?v=4">
@endpush
@section('main')
@php
    // A trigger opens the booking dialog when the form exists, otherwise falls back to email.
    $btnInner = fn (string $label) => '<span class="btn-text"><span>' . e($label) . '</span></span><span class="btn-icon"><i class="tji-arrow-right-long"></i></span>';
    $cta = fn (string $label, string $class = '') => new \Illuminate\Support\HtmlString(
        $hasForm
            ? '<button type="button" class="tj-primary-btn ' . $class . '" data-open-booking>' . $btnInner($label) . '</button>'
            : '<a class="tj-primary-btn ' . $class . '" href="' . e($mailto) . '">' . $btnInner($label) . '</a>'
    );
@endphp
<div class="fe-consultancy">

    <section class="fe-c-hero" id="top">
        <div class="fe-c-wrap fe-c-hero-grid">
            <div class="fe-c-hero-copy">
                <p class="fe-c-eyebrow">Information security consulting for businesses</p>
                <h1>You've built your business. <em>How well is it protected?</em></h1>
                <p class="fe-c-lead">Your customer data, financial information, employees and day-to-day operations all deserve proper protection. Let's understand where your business stands—and what deserves attention.</p>
                <div class="fe-c-actions">
                    {{ $cta('Book your free discovery call') }}
                    <a class="tj-primary-btn transparent-btn" href="#how"><span class="btn-text"><span>See how it works</span></span><span class="btn-icon"><i class="tji-arrow-right-long"></i></span></a>
                </div>
                <ul class="fe-c-micro">
                    <li>✓ Free 30-minute conversation</li>
                    <li>✓ No payment required</li>
                    <li>✓ SMEs &amp; larger organisations</li>
                </ul>
            </div>

            <div class="fe-c-visual">
                @if ($videoId)
                <div class="fe-c-player" data-video="{{ $videoId }}">
                    <div class="fe-c-stage" data-stage>
                        <img class="fe-c-poster" src="https://i.ytimg.com/vi/{{ $videoId }}/maxresdefault.jpg" onerror="this.onerror=null;this.src='https://i.ytimg.com/vi/{{ $videoId }}/hqdefault.jpg'" alt="" width="1280" height="720" fetchpriority="high">
                        <div class="fe-c-shade" aria-hidden="true"></div>
                        <button type="button" class="fe-c-hit" data-hit aria-label="Pause or resume the video" hidden></button>
                        <div class="fe-c-hint" data-hint hidden aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9.500v5h4l5 4v-13l-5 4z" fill="currentColor"/><path d="M16 9.500l5 5M21 9.500l-5 5"/></svg>Playing without sound. Tap the video to hear it.</div>
                        <button type="button" class="fe-c-play" data-play aria-label="Play: Meet the consultant behind your first conversation">
                            <svg viewBox="0 0 24 24" width="30" height="30" aria-hidden="true"><path d="M8 5.500v13l11-6.500z" fill="currentColor"/></svg>
                        </button>
                        <div class="fe-c-caption" data-caption>
                            <strong>Meet the consultant behind your first conversation.</strong>
                            <span>Forward Edge Consulting</span>
                        </div>
                        <div class="fe-c-controls" data-controls hidden>
                            <button type="button" data-toggle aria-label="Pause video"><svg class="is-pause" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M7 5h3.500v14H7zM13.500 5H17v14h-3.500z" fill="currentColor"/></svg><svg class="is-resume" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M8 5.500v13l11-6.500z" fill="currentColor"/></svg></button>
                            <button type="button" class="fe-c-sound" data-sound aria-pressed="false"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9.500v5h4l5 4v-13l-5 4z" fill="currentColor"/><path class="is-waves" d="M16.500 9a4 4 0 0 1 0 6M19 6.500a8 8 0 0 1 0 11"/><path class="is-off" d="M16 9.500l5 5M21 9.500l-5 5"/></svg><span data-sound-label>Turn sound on</span></button>
                            <button type="button" class="fe-c-full" data-full aria-label="Full screen"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg></button>
                        </div>
                    </div>
                </div>
                @else
                <div class="fe-c-player"><div class="fe-c-stage"><img class="fe-c-poster" src="{{ asset('frontend/assets/images/hero/bootcamp-community.jpg') }}" alt="Participants at a Forward Edge cybersecurity bootcamp, smiling together" width="1024" height="1024" fetchpriority="high"></div></div>
                @endif
                <div class="fe-c-floating">
                    <span class="fe-c-floating-icon">{{ $i('shield') }}</span>
                    <span><b>Start with clarity, not assumptions.</b><small>Practical guidance for your business</small></span>
                </div>
            </div>
        </div>
    </section>

    <div class="fe-c-strip"><div class="fe-c-wrap"><span>BUSINESS-FIRST ADVICE</span><span><b>PHYSICAL + DIGITAL</b> SECURITY</span><span>CLEAR NEXT STEPS</span><span>NO OBLIGATION</span></div></div>

    <section class="fe-c-section is-soft" id="how" aria-labelledby="fe-c-how">
        <div class="fe-c-wrap">
            <div class="fe-c-head">
                <p class="fe-c-eyebrow">Simple from the first conversation</p>
                <h2 id="fe-c-how">Here's what happens when you book.</h2>
                <p>No complicated process. Just a straightforward conversation about your business and the security challenges that matter to you.</p>
            </div>
            <ol class="fe-c-steps">
                @foreach ($steps as $n => [$icon, $title, $text])
                    <li>
                        <span class="fe-c-num">STEP {{ sprintf('%02d', $n + 1) }}</span>
                        <span class="fe-c-tile">{{ $i($icon) }}</span>
                        <h3>{{ $title }}</h3>
                        <p>{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
            <div class="fe-c-after">
                {{ $cta('Choose your free appointment') }}
                <small>30 minutes · Online · No obligation to purchase</small>
            </div>
        </div>
    </section>

    <section class="fe-c-section" aria-labelledby="fe-c-why">
        <div class="fe-c-wrap fe-c-split">
            <figure class="fe-c-photo">
                <img src="{{ asset('frontend/assets/images/hero/consulting-team.jpg') }}" alt="A team in conversation around a meeting-room table" width="1400" height="933" loading="lazy">
                <figcaption>Your business deserves more than guesswork ↗</figcaption>
            </figure>
            <div>
                <p class="fe-c-eyebrow">Why businesses speak to us</p>
                <h2 id="fe-c-why">Security gaps don't always announce themselves.</h2>
                <p class="fe-c-body">A payment instruction that looks genuine. An employee who still has access after leaving. Customer records stored without clear safeguards. Small weaknesses can create expensive problems.</p>
                <ul class="fe-c-checks">
                    @foreach ($questions as $q)<li>{{ $q }}</li>@endforeach
                </ul>
                {{ $cta('Let\'s discuss your business') }}
            </div>
        </div>
    </section>

    <section class="fe-c-section is-soft" id="services" aria-labelledby="fe-c-services">
        <div class="fe-c-wrap">
            <div class="fe-c-head">
                <p class="fe-c-eyebrow">How Forward Edge can help</p>
                <h2 id="fe-c-services">Professional security support, built around your business.</h2>
                <p>Whether you're growing a small team or managing a larger organisation, the right security decisions start with understanding your real risks.</p>
            </div>
            <div class="fe-c-cards">
                @foreach ($services as [$icon, $title, $text])
                    <article><span class="fe-c-tile">{{ $i($icon) }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p></article>
                @endforeach
            </div>
            <div class="fe-c-after">{{ $cta('Book a free call to discuss your needs') }}</div>
        </div>
    </section>

    <section class="fe-c-section" aria-labelledby="fe-c-band">
        <div class="fe-c-wrap">
            <div class="fe-c-band">
                <div>
                    <p class="fe-c-eyebrow">For small teams and complex organisations</p>
                    <h2 id="fe-c-band">Different businesses. Different risks. A practical starting point for both.</h2>
                    <p>From growing SMEs to established organisations, we start by understanding how you work—not by pushing a one-size-fits-all package.</p>
                </div>
                {{ $cta('Talk to Forward Edge', 'is-gold') }}
            </div>
        </div>
    </section>

    <section class="fe-c-section is-tint" id="about" aria-labelledby="fe-c-about">
        <div class="fe-c-wrap fe-c-split {{ $photo ? 'has-portrait' : 'has-pullquote' }}">
            @if ($photo)
                <figure class="fe-c-portrait"><img src="{{ asset($photo) }}" alt="{{ $consultant }}" width="640" height="800" loading="lazy"></figure>
            @endif
            <div>
                <p class="fe-c-eyebrow">The person behind the conversation</p>
                <h2 id="fe-c-about">Meet {{ $consultant }}. You may know him as CyberMo.</h2>
                <p class="fe-c-body">CyberMo is Moyosore's professional content identity. Forward Edge Consulting is the firm through which businesses engage for information security consulting services.</p>
                <p class="fe-c-body">The goal is straightforward: help business leaders understand their security challenges in plain language and make better-informed decisions about what to do next.</p>
                @if ($photo)
                    <blockquote class="fe-c-quote">“Good security advice should make the next decision clearer—not leave you more confused.”</blockquote>
                @endif
                {{ $cta('Book a conversation with CyberMo') }}
            </div>
            @unless ($photo)
                <blockquote class="fe-c-quote is-large">“Good security advice should make the next decision clearer—not leave you more confused.”</blockquote>
            @endunless
        </div>
    </section>

    <section class="fe-c-section" aria-labelledby="fe-c-scope">
        <div class="fe-c-wrap fe-c-split">
            <div>
                <p class="fe-c-eyebrow">Free conversation vs professional engagement</p>
                <h2 id="fe-c-scope">What you get from the free call—and what comes later.</h2>
                <p class="fe-c-body">Your free discovery call is designed to understand your needs and offer initial direction. It is not a penetration test, formal audit or verified security assessment.</p>
                <ul class="fe-c-checks">
                    @foreach ($scope as [$lead, $rest])<li><b>{{ $lead }}</b> {{ $rest }}</li>@endforeach
                </ul>
                {{ $cta('Start with a free call') }}
            </div>
            <figure class="fe-c-photo">
                <img src="{{ asset('frontend/assets/images/hero/security-monitoring.jpg') }}" alt="A security analyst reviewing data dashboards on two monitors" width="1400" height="935" loading="lazy">
                <figcaption>Clear scope. Useful outcomes. No pressure.</figcaption>
            </figure>
        </div>
    </section>

    <section class="fe-c-section is-soft" id="faq" aria-labelledby="fe-c-faq">
        <div class="fe-c-wrap fe-c-faq-wrap">
            <div class="fe-c-head">
                <p class="fe-c-eyebrow">Questions before you book?</p>
                <h2 id="fe-c-faq">Good questions deserve clear answers.</h2>
            </div>
            <div class="fe-c-faq">
                <details open><summary>Is the first discovery call really free?</summary><p>Yes. The initial 30-minute online conversation is free. Any assessment or additional consulting work is separately scoped and quoted.</p></details>
                <details><summary>Will I receive a full security assessment during the call?</summary><p>No. The call is an initial discussion. A formal assessment involves a defined scope and review of relevant evidence and is a paid professional service.</p></details>
                <details><summary>Can a small business book, or is this for large organisations only?</summary><p>Both. We work from your organisation's actual needs, whether you run a small business or oversee a larger operation.</p></details>
                <details><summary>What happens after I book?</summary><p>You will receive a confirmation and meeting details once we have agreed a time. You may also be asked a few brief questions to help prepare for the conversation.</p></details>
                <details><summary>Do I have to purchase anything after the call?</summary><p>No. There is no obligation to proceed with a paid service.</p></details>
            </div>
        </div>
    </section>

    <section class="fe-c-section fe-c-last" id="book" aria-labelledby="fe-c-close">
        <div class="fe-c-wrap">
            <div class="fe-c-band is-deep">
                <div>
                    <p class="fe-c-eyebrow">Your next step is simple</p>
                    <h2 id="fe-c-close">Know what deserves attention before it becomes a bigger problem.</h2>
                    <p>Choose a convenient time for a free 30-minute security discovery call with Forward Edge Consulting.</p>
                </div>
                {{ $cta('Book your free appointment', 'is-gold') }}
            </div>
        </div>
    </section>

    @if ($hasForm)
        <div class="fe-c-sticky">{{ $cta('Book your free discovery call') }}</div>

        <dialog class="fe-c-dialog" id="booking" aria-labelledby="fe-c-booking-title">
            <div class="fe-c-dialog-body">
                <button type="button" class="fe-c-close" data-close-booking aria-label="Close booking">×</button>
                <div data-book-form>
                    <p class="fe-c-eyebrow">Forward Edge Consulting</p>
                    <h2 id="fe-c-booking-title">Book a free security discovery call</h2>
                    <p class="fe-c-dialog-sub">Select a preferred date and time, then tell us about your organisation.</p>
                    @include('user.partials.form-embed', ['form' => $callForm])
                </div>
                <div class="fe-c-booked" data-booked hidden>
                    <span class="fe-c-booked-tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.500 4.500L19 7.500"/></svg></span>
                    <h2>Your session request has been received</h2>
                    <p class="fe-c-booked-when" data-booked-when hidden></p>
                    <p>The Forward Edge team will reach out to confirm your appointment. We have also sent you an email, so please check your inbox. Your call is confirmed once you receive our confirmation email with the meeting details.</p>
                    <button type="button" class="tj-primary-btn transparent-btn" data-close-booking><span class="btn-text"><span>Close</span></span><span class="btn-icon"><i class="tji-arrow-right-long"></i></span></button>
                </div>
            </div>
        </dialog>
    @endif
</div>
@endsection
@push('scripts')
<script>
(function () {
    var dlg = document.getElementById('booking');
    if (dlg) {
        document.querySelectorAll('[data-open-booking]').forEach(function (b) {
            b.addEventListener('click', function () { if (!dlg.open) dlg.showModal(); });
        });
        dlg.querySelectorAll('[data-close-booking]').forEach(function (b) { b.addEventListener('click', function () { dlg.close(); }); });
        dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
        dlg.addEventListener('fe-form:success', function (e) {
            dlg.querySelector('[data-book-form]').hidden = true;
            var done = dlg.querySelector('[data-booked]'), when = done.querySelector('[data-booked-when]'), b = e.detail && e.detail.booking;
            if (when && b && b.when) { when.textContent = 'Requested time: ' + b.when; when.hidden = false; }
            done.hidden = false;
            dlg.querySelector('.fe-c-dialog-body').scrollTop = 0;
        });
    }

    var box = document.querySelector('[data-video]');
    if (!box) return;
    var id = box.dataset.video, stage = box.querySelector('[data-stage]');
    var hit = box.querySelector('[data-hit]'), hint = box.querySelector('[data-hint]');
    var playBtn = box.querySelector('[data-play]'), controls = box.querySelector('[data-controls]');
    var toggle = box.querySelector('[data-toggle]'), sound = box.querySelector('[data-sound]'), label = box.querySelector('[data-sound-label]');
    var frame = null, playing = false, muted = true;

    var send = function (fn, args) {
        if (frame) frame.contentWindow.postMessage(JSON.stringify({ event: 'command', func: fn, args: args || '' }), '*');
    };
    var paint = function () {
        box.classList.toggle('is-paused', !playing);
        toggle.setAttribute('aria-label', playing ? 'Pause video' : 'Play video');
        sound.setAttribute('aria-pressed', String(!muted));
        sound.classList.toggle('is-muted', muted);
        label.textContent = muted ? 'Turn sound on' : 'Sound on';
        hint.hidden = !(muted && playing);
    };
    var setMuted = function (m) { send(m ? 'mute' : 'unMute'); if (!m) send('setVolume', 100); muted = m; paint(); };
    var start = function (startMuted) {
        if (frame) return;
        muted = startMuted; playing = true;
        frame = document.createElement('iframe');
        frame.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&mute=' + (muted ? 1 : 0) + '&controls=0&disablekb=1&playsinline=1&rel=0&iv_load_policy=3&loop=1&playlist=' + id + '&enablejsapi=1&modestbranding=1';
        frame.title = 'Forward Edge security consulting video';
        frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        frame.referrerPolicy = 'strict-origin-when-cross-origin';
        stage.insertBefore(frame, stage.querySelector('.fe-c-shade'));
        box.classList.add('is-started');
        controls.hidden = false; hit.hidden = false;
        paint();
    };
    playBtn.addEventListener('click', function () { if (frame) { send('playVideo'); playing = true; paint(); } else start(false); });
    toggle.addEventListener('click', function () { send(playing ? 'pauseVideo' : 'playVideo'); playing = !playing; paint(); });
    sound.addEventListener('click', function () { setMuted(!muted); });
    // Clicking the picture never leaves the site: first click turns the sound on, later clicks pause or resume.
    hit.addEventListener('click', function () {
        if (muted) { setMuted(false); return; }
        send(playing ? 'pauseVideo' : 'playVideo'); playing = !playing; paint();
    });
    // Browsers only allow sound after a gesture, so the first click or key press while the video is on screen turns it on.
    var onScreen = false;
    if ('IntersectionObserver' in window) new IntersectionObserver(function (e) { onScreen = e[0].isIntersecting; }, { threshold: .5 }).observe(box);
    var firstGesture = function (e) {
        if (!frame || !muted || !onScreen || e.target.closest('[data-controls],[data-hit],[data-play]')) return;
        setMuted(false);
    };
    ['pointerdown', 'keydown'].forEach(function (t) { document.addEventListener(t, firstGesture, { once: true, capture: true }); });
    box.querySelector('[data-full]').addEventListener('click', function () {
        if (document.fullscreenElement) document.exitFullscreen(); else if (stage.requestFullscreen) stage.requestFullscreen();
    });
    // Muted autoplay is the only kind browsers allow. Visitors who prefer reduced motion press play instead.
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        var go = function () { start(true); };
        if (document.readyState === 'complete') go(); else window.addEventListener('load', go);
    }
})();
</script>
@endpush
