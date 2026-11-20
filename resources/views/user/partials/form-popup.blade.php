{{-- The "check your email / download" popup shown after a form with a resource is sent.
     It lives on every page so a form can redirect first and the popup opens on arrival (flashed as form_popup). --}}
@php $flashedPopup = session('form_popup'); @endphp
@if ($flashedPopup)
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/forms.css') }}?v=4">
@endif
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
    if (!d || !d.showModal || !p) return;
    d.querySelector('#fe-popup-title').textContent = p.title || '';
    d.querySelector('[data-popup-message]').textContent = p.message || '';
    var a = d.querySelector('[data-popup-download]');
    a.href = p.download_url; a.textContent = p.button || 'Download your resource';
    if (!d.open) d.showModal();
};
document.addEventListener('click', function (e) {
    var d = document.getElementById('fe-popup');
    if (!d) return;
    if (e.target.closest('[data-popup-close]') || e.target === d) d.close();
});
@if ($flashedPopup)
window.feShowPopup(@json($flashedPopup));
@endif
</script>
