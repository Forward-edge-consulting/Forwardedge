/* Footer, blog and page-builder signup forms. Sends answers plus recorded consent to /newsletter/subscribe. */
            document.addEventListener('submit', async (event) => {
                const form = event.target.closest('.js-newsletter-form');
                if (!form || event.target !== form) {
                    return;
                }

                event.preventDefault();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                if (form.dataset.submitting === '1') return;
                form.dataset.submitting = '1';

                const submitBtn = form.querySelector('button[type="submit"]');
                const feedback = form.querySelector('.dynamic-form-feedback');
                const blockId = form.dataset.blockId;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                    || form.querySelector('input[name="_token"]')?.value;

                const fields = Array.from(form.querySelectorAll('[data-field-input]')).map((input) => ({
                    label: input.dataset.fieldLabel || '',
                    name: input.dataset.fieldName,
                    type: input.dataset.fieldType,
                    required: input.dataset.fieldRequired === '1',
                    value: input.value.trim(),
                }));

                const honeypot = form.querySelector('input[name="hp_field"]')?.value || '';
                const consentBox = form.querySelector('input[type="checkbox"][data-consent]');

                if (window.FELoader && FELoader.hasWidget(form) && !FELoader.token(form)) {
                    showFormFeedback(feedback, 'Verifying your browser. Please try again in a moment.', 'error');
                    delete form.dataset.submitting;
                    return;
                }

                const payload = {
                    'cf-turnstile-response': window.FELoader ? FELoader.token(form) : '',
                    source: 'builder_form',
                    block_id: blockId,
                    hp_field: honeypot,
                    consent: consentBox ? consentBox.checked : null,
                    consent_text: consentBox && consentBox.checked ? consentBox.dataset.consent : null,
                    fields,
                    tags: JSON.parse(form.dataset.tags || '[]'),
                };

                if (window.FELoader) FELoader.start(submitBtn);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });

                    const body = await response.json().catch(() => ({}));

                    if (response.ok) {
                        form.reset();
                        showFormFeedback(feedback, body.message || 'Thanks for subscribing!', 'success');
                    } else {
                        showFormFeedback(feedback, body.message || 'Something went wrong. Please retry.', 'error');
                    }
                } catch (error) {
                    showFormFeedback(feedback, 'Network error. Please try again.', 'error');
                } finally {
                    if (window.FELoader) { FELoader.stop(submitBtn); FELoader.reset(form); }
                    delete form.dataset.submitting;
                }
            });

            function showFormFeedback(el, message, state) {
                if (window.toastr) {
                    if (state === 'success') {
                        toastr.success(message);
                    } else {
                        toastr.error(message);
                    }
                } else {
                    alert(message);
                }

                if (el) {
                    el.textContent = '';
                    el.classList.remove('success', 'error', 'is-visible');
                }
            }

            function ensureNewsletterModal() {
                let modal = document.querySelector('[data-dynamic-form-modal]');
                if (modal) return modal;

                modal = document.createElement('div');
                modal.className = 'dynamic-form-modal';
                modal.setAttribute('data-dynamic-form-modal', 'true');
                modal.setAttribute('hidden', 'hidden');
                modal.innerHTML = `
  <div class="dynamic-form-modal__dialog" role="dialog" aria-modal="true">
    <button type="button" class="dynamic-form-modal__close" data-modal-dismiss aria-label="Close">&times;</button>
    <div class="dynamic-form-modal__icon success" data-modal-icon>✔</div>
    <h3 class="dynamic-form-modal__title" data-modal-title>All set!</h3>
    <p class="dynamic-form-modal__message" data-modal-message>Thanks for reaching out!</p>
    <div class="mt-3">
      <button type="button" class="tj-primary-btn" data-modal-dismiss>
        <span class="btn-text"><span>Close</span></span>
      </button>
    </div>
  </div>`;
                document.body.appendChild(modal);

                modal.addEventListener('click', (ev) => {
                    if (ev.target === modal || ev.target.hasAttribute('data-modal-dismiss')) {
                        closeNewsletterModal();
                    }
                });

                document.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Escape') {
                        closeNewsletterModal();
                    }
                });

                return modal;
            }

            function openNewsletterModal(message, state) {
                const modal = ensureNewsletterModal();
                const dialog = modal.querySelector('.dynamic-form-modal__dialog');
                const icon = modal.querySelector('[data-modal-icon]');
                const text = modal.querySelector('[data-modal-message]');
                const title = modal.querySelector('[data-modal-title]');

                if (icon) {
                    icon.classList.remove('success', 'error');
                    icon.classList.add(state === 'error' ? 'error' : 'success');
                    icon.textContent = state === 'error' ? '✕' : '✔';
                }

                if (text) text.textContent = message;
                if (title) {
                    title.textContent = state === 'error'
                        ? 'Something went wrong'
                        : "You're in!";
                }

                if (dialog) {
                    dialog.classList.toggle('dynamic-form-modal__dialog--dark', document.body.classList.contains('dark-mode'));
                }

                if (modal.dataset.timerId) {
                    clearTimeout(Number(modal.dataset.timerId));
                    delete modal.dataset.timerId;
                }

                modal.removeAttribute('hidden');
                document.body.classList.add('dynamic-form-modal-open');

                const timeout = setTimeout(() => {
                    closeNewsletterModal();
                }, state === 'error' ? 8000 : 5000);

                modal.dataset.timerId = timeout;
            }

            function closeNewsletterModal() {
                const modal = document.querySelector('[data-dynamic-form-modal]');
                if (modal) {
                    modal.setAttribute('hidden', 'hidden');
                    if (modal.dataset.timerId) {
                        clearTimeout(Number(modal.dataset.timerId));
                        delete modal.dataset.timerId;
                    }
                }
                document.body.classList.remove('dynamic-form-modal-open');
            }
        
