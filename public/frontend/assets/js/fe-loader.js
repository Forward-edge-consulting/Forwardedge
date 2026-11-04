/* Shared button loader and Turnstile helpers for public forms. */
(function () {
    function wrapText(btn) {
        Array.from(btn.childNodes).forEach(function (n) {
            if (n.nodeType === 3 && n.textContent.trim()) {
                var s = document.createElement('span');
                s.className = 'fe-btn-label';
                btn.replaceChild(s, n);
                s.appendChild(n);
            }
        });
    }

    var FELoader = {
        start: function (btn) {
            if (!btn || btn.classList.contains('fe-is-loading')) return;
            wrapText(btn);
            var s = document.createElement('span');
            s.className = 'fe-loader';
            s.setAttribute('aria-hidden', 'true');
            btn.appendChild(s);
            btn.classList.add('fe-is-loading');
            btn.setAttribute('aria-busy', 'true');
            btn.disabled = true;
        },
        stop: function (btn) {
            if (!btn) return;
            var s = btn.querySelector(':scope > .fe-loader');
            if (s) s.remove();
            btn.classList.remove('fe-is-loading');
            btn.removeAttribute('aria-busy');
            btn.disabled = false;
        },
        // Turnstile tokens are single use: call reset after every attempt.
        token: function (form) {
            var f = form.querySelector('[name="cf-turnstile-response"]');
            return f ? f.value : '';
        },
        hasWidget: function (form) { return !!form.querySelector('.cf-turnstile'); },
        reset: function (form) {
            var w = form.querySelector('.cf-turnstile');
            if (w && window.turnstile) { try { window.turnstile.reset(w); } catch (e) {} }
        }
    };
    window.FELoader = FELoader;

    // Plain (non-AJAX) forms opt in with data-fe-loader; the button spins until the next page loads.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-fe-loader]')) return;
        setTimeout(function () {
            if (e.defaultPrevented) return;
            FELoader.start(form.querySelector('button[type="submit"]'));
        }, 0);
    });
    // Back button restores the page from cache with the button still locked.
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-fe-loader] .fe-is-loading').forEach(FELoader.stop);
    });
})();
