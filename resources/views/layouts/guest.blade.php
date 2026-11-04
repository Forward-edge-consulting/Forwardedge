<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>Sign in | {{ config('app.name', 'Forward Edge') }}</title>
        <link rel="icon" href="{{ asset('frontend/assets/images/fav.png') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="{{ asset('backend/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
        <link rel="stylesheet" href="{{ asset('backend/assets/css/fe-tokens.css') }}">
        <link rel="stylesheet" href="{{ asset('backend/assets/css/fe-auth.css') }}?v=5">
    </head>
    <body class="fe-auth">
        <div class="fe-auth-split">
            <aside class="fe-auth-aside" aria-hidden="true">
                <div class="fe-auth-brand">
                    <img src="{{ asset('frontend/assets/images/logos/logo-on-dark.png') }}" alt="" width="112" height="58">
                    <small>Website admin</small>
                </div>
                <div>
                    <h2>Run the whole website from one place.</h2>
                    <ul>
                        <li><i class="bi bi-file-earmark-richtext"></i><span>Build pages with a live preview</span></li>
                        <li><i class="bi bi-journal-text"></i><span>Write posts that collect subscribers</span></li>
                        <li><i class="bi bi-calendar-event"></i><span>Show off events and programmes</span></li>
                        <li><i class="bi bi-inbox"></i><span>Keep every lead, ready for Brevo</span></li>
                    </ul>
                </div>
                <p class="fe-auth-small">Forward Edge Consulting</p>
            </aside>

            <main class="fe-auth-main">
                <div class="fe-auth-panel">
                    <a class="fe-auth-logo" href="{{ route('home') }}" aria-label="Forward Edge, back to site">
                        <img src="{{ asset('frontend/assets/images/logos/logo.png') }}" alt="Forward Edge">
                    </a>
                    <div class="fe-auth-card">
                        {{ $slot }}
                    </div>
                    <p class="fe-auth-foot">Admin access only. <a href="{{ route('home') }}">Back to site</a></p>
                </div>
            </main>
        </div>
    </body>
</html>
