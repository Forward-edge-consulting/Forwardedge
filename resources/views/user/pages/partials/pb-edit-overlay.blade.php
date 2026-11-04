{{-- Only rendered in the page builder's preview (?pb_edit=1). Clicking a section asks the editor to open it. --}}
<style>
    [data-pb-block] { position: relative; cursor: pointer; }
    [data-pb-block].pb-hover { outline: 2px solid #1a76a8; outline-offset: -2px; }
    [data-pb-block].pb-hover::after {
        content: "Edit " attr(data-pb-type); position: absolute; top: 8px; left: 8px; z-index: 50;
        padding: 4px 10px; border-radius: 999px; background: #1a76a8; color: #fff;
        font: 600 12px/1.4 system-ui, sans-serif; text-transform: capitalize; pointer-events: none;
    }
    [data-pb-block].pb-selected { outline: 3px solid #1a76a8; outline-offset: -3px; }
</style>
<script>
(function () {
    var last = null;
    document.addEventListener('mouseover', function (e) {
        var el = e.target.closest('[data-pb-block]');
        if (last && last !== el) last.classList.remove('pb-hover');
        if (el) el.classList.add('pb-hover');
        last = el;
    });
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-pb-block]');
        if (!el) return;
        e.preventDefault(); e.stopPropagation();
        window.parent.postMessage({ pbEdit: parseInt(el.getAttribute('data-pb-block'), 10) }, window.location.origin);
    }, true);
    document.addEventListener('submit', function (e) { e.preventDefault(); }, true);
    window.addEventListener('message', function (e) {
        if (e.origin !== window.location.origin || !e.data || e.data.pbSelect === undefined) return;
        document.querySelectorAll('.pb-selected').forEach(function (n) { n.classList.remove('pb-selected'); });
        var t = document.querySelector('[data-pb-block="' + e.data.pbSelect + '"]');
        if (t) { t.classList.add('pb-selected'); t.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    });
})();
</script>
