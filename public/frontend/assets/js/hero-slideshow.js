(() => {
    document.querySelectorAll('[data-hero-slideshow]').forEach(carousel => {
        const slides = [...carousel.querySelectorAll('.fe-hero-slide')];
        const caption = carousel.querySelector('[data-slide-caption]');
        const toggle = carousel.querySelector('[data-slide-toggle]');
        const status = carousel.querySelector('[data-slide-status]');
        const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
        let current = 0;
        let paused = reducedMotion.matches;
        let focused = false;
        let visible = true;
        let timer;
        carousel.querySelector('.fe-hero-controls').hidden = false;
        const updateToggle = () => {
            toggle.textContent = paused ? 'Play' : 'Pause';
            toggle.setAttribute('aria-label', paused ? 'Play slideshow' : 'Pause slideshow');
        };
        const schedule = () => {
            clearTimeout(timer);
            if (!paused && !focused && visible && !document.hidden) {
                timer = setTimeout(() => show(current + 1), 5000);
            }
        };
        const show = (index, manual = false, instant = false) => {
            const next = (index + slides.length) % slides.length;
            const image = slides[next];
            if (!image.complete || !image.naturalWidth) { schedule(); return; }
            carousel.classList.toggle('is-instant', instant);
            slides[current].classList.remove('is-active');
            slides[current].setAttribute('aria-hidden', 'true');
            image.classList.add('is-active');
            image.removeAttribute('aria-hidden');
            current = next;
            caption.textContent = image.dataset.caption;
            if (manual) status.textContent = `Image ${current + 1} of ${slides.length}: ${image.dataset.caption}`;
            schedule();
        };
        carousel.querySelector('[data-slide-prev]').addEventListener('click', event => show(current - 1, true, event.detail === 0));
        carousel.querySelector('[data-slide-next]').addEventListener('click', event => show(current + 1, true, event.detail === 0));
        toggle.addEventListener('click', () => { paused = !paused; if (!paused) focused = false; updateToggle(); schedule(); });
        carousel.addEventListener('focusin', () => { focused = true; schedule(); });
        carousel.addEventListener('focusout', event => {
            if (!carousel.contains(event.relatedTarget)) { focused = false; schedule(); }
        });
        document.addEventListener('visibilitychange', schedule);
        reducedMotion.addEventListener('change', () => { paused = reducedMotion.matches; updateToggle(); schedule(); });
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(entries => { visible = entries[0].isIntersecting; schedule(); }).observe(carousel);
        }
        updateToggle();
        schedule();
    });
})();
