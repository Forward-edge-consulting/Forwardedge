@php
    use Illuminate\Support\Facades\Crypt;
    $formId = 'fe-form-' . $form->id . '-' . \Illuminate\Support\Str::random(4);
    $consentRequired = (bool) $form->setting('consent_required');
@endphp

@if (!$form->isOpen())
    <p class="fe-form-closed">This form is no longer accepting responses.</p>
@else
    <form id="{{ $formId }}" class="fe-form" method="POST" action="{{ route('forms.submit', $form->slug) }}" data-fe-form novalidate>
        @csrf
        <input type="hidden" name="_ts" value="{{ Crypt::encryptString((string) time()) }}">
        <input type="text" name="hp_field" tabindex="-1" autocomplete="off" class="fe-form-hp" aria-hidden="true">

        @foreach ($form->fields as $f)
            @php $id = $formId . '_' . $f['key']; $name = 'answers[' . $f['key'] . ']'; @endphp
            <div class="fe-form-field" data-field="{{ $f['key'] }}">
                @if ($f['type'] === 'slot')
                    <div class="fe-slots" data-slot-picker data-src="{{ route('booking.availability') }}">
                        <span class="fe-form-label" id="{{ $id }}_l">{{ $f['label'] }}@if ($f['required']) <span aria-hidden="true">*</span>@endif</span>
                        <input type="hidden" name="{{ $name }}" data-slot-value>
                        <div class="fe-slot-days" role="radiogroup" aria-labelledby="{{ $id }}_l"></div>
                        <div class="fe-slot-times" role="radiogroup" aria-label="Times" hidden></div>
                        <p class="fe-slot-note" data-slot-note>Loading available times…</p>
                    </div>
                @elseif (in_array($f['type'], ['radio', 'checkbox'], true))
                    <fieldset>
                        <legend class="fe-form-label">{{ $f['label'] }}@if ($f['required']) <span aria-hidden="true">*</span>@endif</legend>
                        @foreach ($f['options'] as $i => $option)
                            <label class="fe-form-choice" for="{{ $id }}_{{ $i }}">
                                <input id="{{ $id }}_{{ $i }}" type="{{ $f['type'] }}" name="{{ $name }}{{ $f['type'] === 'checkbox' ? '[]' : '' }}" value="{{ $option }}"
                                       @if ($f['type'] === 'radio' && $f['required']) required @endif>
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                @else
                    <label class="fe-form-label" for="{{ $id }}">{{ $f['label'] }}@if ($f['required']) <span aria-hidden="true">*</span>@endif</label>
                    @if ($f['type'] === 'textarea')
                        <textarea id="{{ $id }}" name="{{ $name }}" rows="4" placeholder="{{ $f['placeholder'] }}" @required($f['required']) maxlength="5000"></textarea>
                    @elseif ($f['type'] === 'select')
                        <select id="{{ $id }}" name="{{ $name }}" class="fe-native" @required($f['required'])>
                            <option value="">Choose…</option>
                            @foreach ($f['options'] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
                        </select>
                    @else
                        <input id="{{ $id }}" type="{{ in_array($f['type'], ['email', 'tel', 'number', 'date'], true) ? $f['type'] : 'text' }}" name="{{ $name }}"
                               placeholder="{{ $f['placeholder'] }}" @required($f['required'])
                               @if ($f['type'] === 'email') autocomplete="email" inputmode="email" @endif
                               @if ($f['type'] === 'tel') autocomplete="tel" inputmode="tel" @endif>
                    @endif
                @endif
                @if ($f['help'])<div class="fe-form-help">{{ $f['help'] }}</div>@endif
                <div class="fe-form-error" data-err="{{ $f['key'] }}" role="alert"></div>
            </div>
        @endforeach

        @if ($consentRequired)
            <div class="fe-form-field" data-field="consent">
                <label class="fe-form-choice fe-form-consent">
                    <input type="checkbox" name="consent" value="1" required>
                    <span>{{ $form->setting('consent_text') }}</span>
                </label>
                <div class="fe-form-error" data-err="consent" role="alert"></div>
            </div>
        @endif

        <x-turnstile />

        <button type="submit" class="fe-form-button"><span class="fe-btn-label">{{ $form->setting('submit_label') }}</span></button>
        <div class="fe-form-feedback" role="status" aria-live="polite">
            @if (session('form_success')) {{ session('form_success') }} @endif
            @if (session('form_error')) {{ session('form_error') }} @endif
        </div>
    </form>

    @once
        @push('scripts')
        <dialog class="fe-popup" id="fe-popup" aria-labelledby="fe-popup-title">
            <div class="fe-popup-body">
                <span class="fe-popup-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
                <h2 id="fe-popup-title"></h2>
                <p data-popup-message></p>
                <a class="fe-popup-download" data-popup-download href="#"></a>
                <button type="button" class="fe-popup-close" data-popup-close>Close</button>
            </div>
        </dialog>
        <script>
        window.feShowPopup = function (p) {
            var d = document.getElementById('fe-popup');
            if (!d || !d.showModal) return;
            d.querySelector('#fe-popup-title').textContent = p.title || '';
            d.querySelector('[data-popup-message]').textContent = p.message || '';
            var a = d.querySelector('[data-popup-download]');
            a.href = p.download_url; a.textContent = 'Download ' + (p.resource_title || 'resource');
            if (!d.open) d.showModal();
        };
        document.addEventListener('click', function (e) {
            var d = document.getElementById('fe-popup');
            if (!d) return;
            if (e.target.closest('[data-popup-close]') || e.target === d) d.close();
        });
        </script>
        <script>
        (function () {
            function load(picker) {
                var days = picker.querySelector('.fe-slot-days'), times = picker.querySelector('.fe-slot-times'),
                    note = picker.querySelector('[data-slot-note]'), value = picker.querySelector('[data-slot-value]');
                value.value = ''; times.hidden = true; times.innerHTML = '';
                note.textContent = 'Loading available times…'; note.classList.add('fe-note-loading');
                days.setAttribute('aria-busy', 'true');
                days.innerHTML = '<span class="fe-skel"></span><span class="fe-skel"></span><span class="fe-skel"></span><span class="fe-skel"></span><span class="fe-skel"></span>';
                return fetch(picker.dataset.src, { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }).then(function (data) {
                    days.innerHTML = '';
                    if (!data.days || !data.days.length) { note.textContent = 'No times are open right now. Please email us and we will arrange one.'; return; }
                    note.textContent = 'Times are shown in ' + data.zone_label + ' (' + data.timezone.replace('_', ' ') + ').';
                    data.days.slice(0, 14).forEach(function (d) {
                        var b = document.createElement('button');
                        b.type = 'button'; b.className = 'fe-slot-day'; b.textContent = d.label; b.setAttribute('role', 'radio'); b.setAttribute('aria-checked', 'false');
                        b.addEventListener('click', function () {
                            days.querySelectorAll('.fe-slot-day').forEach(function (x) { x.classList.remove('is-on'); x.setAttribute('aria-checked', 'false'); });
                            b.classList.add('is-on'); b.setAttribute('aria-checked', 'true');
                            value.value = ''; times.innerHTML = ''; times.hidden = false;
                            d.slots.forEach(function (sl) {
                                var t = document.createElement('button');
                                t.type = 'button'; t.className = 'fe-slot-time'; t.textContent = sl.label; t.setAttribute('role', 'radio'); t.setAttribute('aria-checked', 'false');
                                t.addEventListener('click', function () {
                                    times.querySelectorAll('.fe-slot-time').forEach(function (x) { x.classList.remove('is-on'); x.setAttribute('aria-checked', 'false'); });
                                    t.classList.add('is-on'); t.setAttribute('aria-checked', 'true'); value.value = sl.value;
                                    var err = picker.closest('.fe-form-field').querySelector('.fe-form-error'); if (err) err.textContent = '';
                                });
                                times.appendChild(t);
                            });
                        });
                        days.appendChild(b);
                    });
                }).catch(function () { days.innerHTML = ''; note.textContent = 'We could not load the available times. Please refresh the page.'; })
                .then(function () { note.classList.remove('fe-note-loading'); days.removeAttribute('aria-busy'); });
            }
            window.feReloadSlots = function (form) { form.querySelectorAll('[data-slot-picker]').forEach(load); };
            document.querySelectorAll('[data-slot-picker]').forEach(load);
        })();
        document.addEventListener('submit', async function (event) {
            var form = event.target.closest('[data-fe-form]');
            if (!form || event.target !== form) return;
            event.preventDefault();
            if (form.dataset.busy === '1') return;

            var feedback = form.querySelector('.fe-form-feedback');
            form.querySelectorAll('.fe-form-error').forEach(function (e) { e.textContent = ''; });
            form.classList.remove('is-error', 'is-success');
            var missing = false;
            form.querySelectorAll('[data-slot-picker]').forEach(function (p) {
                if (!p.querySelector('[data-slot-value]').value) {
                    missing = true;
                    var err = p.closest('.fe-form-field').querySelector('.fe-form-error'); if (err) err.textContent = 'Please choose a date and a time.';
                }
            });
            if (missing) return;
            if (!form.checkValidity()) { form.reportValidity(); return; }

            if (window.FELoader && FELoader.hasWidget(form) && !FELoader.token(form)) {
                feedback.textContent = 'Verifying your browser. Please try again in a moment.';
                form.classList.add('is-error');
                return;
            }

            var button = form.querySelector('.fe-form-button');
            form.dataset.busy = '1'; FELoader.start(button);
            try {
                var res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form), credentials: 'same-origin'
                });
                var body = await res.json().catch(function () { return {}; });
                if (res.ok) {
                    form.reset(); form.classList.add('is-success'); if (window.feReloadSlots) window.feReloadSlots(form);
                    form.dispatchEvent(new CustomEvent('fe-form:success', { bubbles: true, detail: body }));
                    feedback.textContent = body.message || 'Thanks.';
                    if (body.popup) window.feShowPopup(body.popup);
                    else if (body.redirect) setTimeout(function () { window.location.href = body.redirect; }, 600);
                } else {
                    form.classList.add('is-error');
                    feedback.textContent = body.message || 'Something went wrong. Please try again.';
                    if (body.refresh_slots && window.feReloadSlots) window.feReloadSlots(form);
                    Object.keys(body.errors || {}).forEach(function (k) {
                        var key = k.replace(/^answers\./, '').split('.')[0];
                        var slot = form.querySelector('[data-err="' + key + '"]');
                        if (slot && !slot.textContent) slot.textContent = body.errors[k][0];
                    });
                }
            } catch (e) {
                form.classList.add('is-error');
                feedback.textContent = 'Network error. Please try again.';
            } finally {
                form.dataset.busy = ''; FELoader.stop(button); FELoader.reset(form);
            }
        });
        </script>
        @endpush
    @endonce
@endif
