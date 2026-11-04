<x-guest-layout>
    <h1 class="fe-auth-title">Sign in</h1>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('admin.login') }}" class="fe-auth-form" id="loginForm" novalidate>
        @csrf

        <div class="fe-field">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false"
                :aria-invalid="$errors->has('email') ? 'true' : 'false'"
                aria-describedby="email-error" />
            <div id="email-error" aria-live="polite">
                <x-input-error :messages="$errors->get('email')" />
            </div>
        </div>

        <div class="fe-field">
            <x-input-label for="password" :value="__('Password')" />
            <div class="fe-input-wrap">
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password"
                    :aria-invalid="$errors->has('password') ? 'true' : 'false'"
                    aria-describedby="password-error" />
                <button type="button" class="fe-toggle" id="passwordToggle" aria-label="Show password" aria-pressed="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <g id="eyeOn">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.36 4.5 12 4.5c4.64 0 8.57 3 9.96 7.18.07.2.07.43 0 .64C20.58 16.49 16.64 19.5 12 19.5c-4.64 0-8.57-3-9.96-7.18Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </g>
                        <path id="eyeOff" hidden stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.22A10.48 10.48 0 0 0 1.93 12C3.23 16.34 7.24 19.5 12 19.5c.99 0 1.95-.14 2.86-.4M6.23 6.23A10.45 10.45 0 0 1 12 4.5c4.76 0 8.77 3.16 10.07 7.5a10.52 10.52 0 0 1-4.3 5.77M6.23 6.23 3 3m3.23 3.23 3.65 3.65m7.89 7.89L21 21m-3.23-3.23-3.65-3.65m0 0a3 3 0 1 0-4.24-4.24m4.24 4.24L9.88 9.88"/>
                    </svg>
                </button>
            </div>
            <div id="password-error" aria-live="polite">
                <x-input-error :messages="$errors->get('password')" />
            </div>
        </div>

        <label class="fe-check">
            <input type="checkbox" name="remember" id="remember">
            <span>{{ __('Keep me signed in on this device') }}</span>
        </label>

        <x-primary-button id="loginButton">
            <span id="buttonText">{{ __('Sign in') }}</span>
            <span class="fe-spinner" id="buttonSpinner" hidden></span>
        </x-primary-button>
    </form>

    <script>
        (function () {
            var form = document.getElementById('loginForm');
            var input = document.getElementById('password');
            var toggle = document.getElementById('passwordToggle');
            var on = document.getElementById('eyeOn');
            var off = document.getElementById('eyeOff');
            var button = document.getElementById('loginButton');
            var text = document.getElementById('buttonText');
            var spinner = document.getElementById('buttonSpinner');

            toggle.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                on.hidden = show;
                off.hidden = !show;
            });

            function reset() {
                button.disabled = false;
                text.textContent = 'Sign in';
                spinner.hidden = true;
            }

            form.addEventListener('submit', function () {
                button.disabled = true;
                text.textContent = 'Signing in';
                spinner.hidden = false;
            });

            // Back button restores the page from cache with the button still locked.
            window.addEventListener('pageshow', reset);
        })();
    </script>
</x-guest-layout>
