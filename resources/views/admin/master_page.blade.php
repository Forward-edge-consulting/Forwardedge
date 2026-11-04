<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport">

  <title>@yield('title', 'Admin portal | Forward Edge')</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <link href="{{ asset('frontend/assets/images/fav.png') }}" rel="icon">
  <link href="{{ asset('backend/assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
      {{-- ✅ CSRF meta for forms & AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- (Optional) If you ALWAYS serve from the same host/path, you can help relative URLs --}}
  {{-- <base href="/"> --}}
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<!-- iziToast CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">

  <link href="{{ asset('backend/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('backend/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

  <link href="{{ asset('backend/assets/css/style.css') }}" rel="stylesheet">
  {{-- <link href="{{ asset('frontend/assets/css/main.css') }}" rel="stylesheet"> --}}
  <link href="{{ asset('backend/assets/css/fe-tokens.css') }}" rel="stylesheet">
  <link href="{{ asset('backend/assets/css/fe-admin.css') }}" rel="stylesheet">
  <link href="{{ asset('backend/assets/css/fe-theme.css') }}?v=5" rel="stylesheet">
  @stack('styles')
</head>

<body class="fe-admin-body">
  <a class="fe-skip" href="#main">Skip to content</a>

  @include('admin.partials.header')
  @include('admin.partials.sidebar')

  <main id="main" class="main fe-admin-content" tabindex="-1">
    <div class="container-fluid px-0">
    @yield('main')
    </div>
  </main>
  {{-- @include('admin.partials.footer') --}}

  
  {{-- Search or jump: Ctrl/Cmd + K --}}
  <div class="fe-palette" id="fePalette" role="dialog" aria-modal="true" aria-label="Search or jump to a page">
    <div class="fe-palette-box">
      <div class="fe-palette-input">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input id="fePaletteInput" type="text" placeholder="Search pages, or type what you want to do" autocomplete="off" spellcheck="false" role="combobox" aria-expanded="true" aria-controls="fePaletteList" aria-autocomplete="list">
      </div>
      <ul class="fe-palette-list" id="fePaletteList" role="listbox"></ul>
      <div class="fe-palette-foot"><span>Up and down to move</span><span>Enter to open</span><span>Esc to close</span></div>
    </div>
  </div>
  @php
    $adminNavigation = [
      ['g' => 'Go to', 'l' => 'Dashboard', 'u' => route('dashboard'), 'i' => 'bi-grid-1x2', 'k' => 'home overview'],
      ['g' => 'Go to', 'l' => 'Pages', 'u' => route('pb.pages'), 'i' => 'bi-file-earmark-richtext', 'k' => 'page builder website'],
      ['g' => 'Go to', 'l' => 'Blog', 'u' => route('admin.blogs.index'), 'i' => 'bi-journal-text', 'k' => 'posts articles'],
      ['g' => 'Go to', 'l' => 'Events', 'u' => route('admin.events.list'), 'i' => 'bi-calendar-event', 'k' => 'programmes bootcamp workshop'],
      ['g' => 'Go to', 'l' => 'Event categories', 'u' => route('admin.event-categories.index'), 'i' => 'bi-tags', 'k' => 'types'],
      ['g' => 'Go to', 'l' => 'Services', 'u' => route('admin.services.index'), 'i' => 'bi-briefcase', 'k' => 'offers'],
      ['g' => 'Go to', 'l' => 'Gallery', 'u' => route('admin.gallery.index'), 'i' => 'bi-images', 'k' => 'photos images'],
      ['g' => 'Go to', 'l' => 'FAQs', 'u' => route('admin.faqs.index'), 'i' => 'bi-question-circle', 'k' => 'questions'],
      ['g' => 'Go to', 'l' => 'Bookings', 'u' => route('admin.bookings.index'), 'i' => 'bi-calendar-check', 'k' => 'calendar discovery calls appointments approve'],
      ['g' => 'Go to', 'l' => 'Booking settings', 'u' => route('admin.bookings.settings'), 'i' => 'bi-clock', 'k' => 'availability hours consultant calendar'],
      ['g' => 'Go to', 'l' => 'Forms', 'u' => route('admin.forms.index'), 'i' => 'bi-ui-checks-grid', 'k' => 'builder newsletter'],
      ['g' => 'Go to', 'l' => 'Responses', 'u' => route('admin.responses.index'), 'i' => 'bi-inbox', 'k' => 'leads brevo export contacts submissions'],
      ['g' => 'Go to', 'l' => 'View the website', 'u' => route('home'), 'i' => 'bi-box-arrow-up-right', 'k' => 'public site', 'x' => true],
      ['g' => 'Create', 'l' => 'New event', 'u' => route('admin.events.create'), 'i' => 'bi-calendar-plus', 'k' => 'add event', 'n' => true],
      ['g' => 'Create', 'l' => 'New blog post', 'u' => route('admin.blogs.store'), 'i' => 'bi-pencil-square', 'k' => 'write article', 'n' => true, 'post' => true],
      ['g' => 'Create', 'l' => 'New page', 'u' => route('pb.pages.create'), 'i' => 'bi-file-earmark-plus', 'k' => 'add page landing', 'n' => true],
      ['g' => 'Create', 'l' => 'New form', 'u' => route('admin.forms.create'), 'i' => 'bi-ui-checks', 'k' => 'add form', 'n' => true],
      ['g' => 'Create', 'l' => 'Add a service', 'u' => route('admin.services.add'), 'i' => 'bi-plus-square', 'k' => 'new service', 'n' => true],
    ];
  @endphp
  <script>
    window.FE_NAV = {{ Illuminate\Support\Js::from($adminNavigation) }};
  </script>

  <!-- iziToast JS -->
<script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
  <script src="{{ asset('backend/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  <script src="{{ asset('backend/assets/js/fe-admin.js') }}"></script>
    @stack('scripts')
  <script>
  (function () {
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) return;

    // axios (if present)
    if (window.axios && window.axios.defaults) {
      window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
      window.axios.defaults.withCredentials = true;
    }

    // fetch wrapper (optional safety net)
    if (!window.csrfFetch) {
      window.csrfFetch = function (input, init) {
        init = init || {};
        init.headers = init.headers || {};
        if (!('X-CSRF-TOKEN' in init.headers)) init.headers['X-CSRF-TOKEN'] = token;
        // Keep cookies (session) on same-origin calls
        if (!('credentials' in init)) init.credentials = 'same-origin';
        return fetch(input, init);
      };
    }
  })();
</script>

@if ($errors->any())
    <script>
        @foreach ($errors->all() as $error)
            iziToast.error({
                title: 'Error',
                message: "{{ $error }}",
                position: 'topRight',
                timeout: 5000,
                progressBar: true,
            });
        @endforeach
    </script>
@endif

@if (session('success'))
    <script>
        iziToast.success({
            title: 'Success',
            message: "{{ session('success') }}",
            position: 'topRight',
            timeout: 4000,
        });
    </script>
@endif

@if (session('error'))
    <script>
        iziToast.error({
            title: 'Error',
            message: "{{ session('error') }}",
            position: 'topRight',
            timeout: 4000,
        });
    </script>
@endif

@if (session('info'))
    <script>
        iziToast.info({
            title: 'Info',
            message: "{{ session('info') }}",
            position: 'topRight',
            timeout: 4000,
        });
    </script>
@endif

@if (session('warning'))
    <script>
        iziToast.warning({
            title: 'Warning',
            message: "{{ session('warning') }}",
            position: 'topRight',
            timeout: 4000,
        });
    </script>
@endif


</body>

</html>
