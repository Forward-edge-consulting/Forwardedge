@php
    // Exactly one item is current.
    $current = match (true) {
        request()->routeIs('dashboard') => 'dashboard',
        request()->routeIs('pb.*') => 'pages',
        request()->routeIs('admin.blogs.*') => 'blog',
        request()->routeIs('admin.events.*', 'admin.event-categories.*') => 'events',
        request()->routeIs('admin.services.*') => 'services',
        request()->routeIs('admin.gallery.*') => 'gallery',
        request()->routeIs('admin.faqs.*') => 'faq',
        request()->routeIs('admin.forms.*') => 'forms',
        request()->routeIs('admin.responses.*') => 'responses',
        request()->routeIs('admin.bookings.*') => 'bookings',
        default => null,
    };
    $pendingBookings = \App\Models\Booking::where('status', 'pending')->count();
    $newLeads = \App\Models\FormSubmission::readyToExport()->count();

    $groups = [
        'Workspace' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid-1x2', 'route' => 'dashboard'],
        ],
        'Content' => [
            ['key' => 'pages',    'label' => 'Pages',    'icon' => 'bi-file-earmark-richtext', 'route' => 'pb.pages'],
            ['key' => 'blog',     'label' => 'Blog',     'icon' => 'bi-journal-text',          'route' => 'admin.blogs.index'],
            ['key' => 'events',   'label' => 'Events',   'icon' => 'bi-calendar-event',        'route' => 'admin.events.list',
                'children' => [['label' => 'Categories', 'route' => 'admin.event-categories.index', 'active' => request()->routeIs('admin.event-categories.*')]]],
            ['key' => 'services', 'label' => 'Services', 'icon' => 'bi-briefcase',             'route' => 'admin.services.index'],
            ['key' => 'gallery',  'label' => 'Gallery',  'icon' => 'bi-images',                'route' => 'admin.gallery.index'],
            ['key' => 'faq',      'label' => 'FAQs',     'icon' => 'bi-question-circle',       'route' => 'admin.faqs.index'],
        ],
        'Leads' => [
            ['key' => 'bookings',  'label' => 'Bookings',  'icon' => 'bi-calendar-check', 'route' => 'admin.bookings.index', 'badge' => $pendingBookings],
            ['key' => 'forms',     'label' => 'Forms',     'icon' => 'bi-ui-checks-grid', 'route' => 'admin.forms.index'],
            ['key' => 'responses', 'label' => 'Responses', 'icon' => 'bi-inbox',          'route' => 'admin.responses.index', 'badge' => $newLeads],
        ],
    ];
    $user = auth()->user();
    $initial = strtoupper(mb_substr($user?->name ?: 'A', 0, 1));
@endphp

<aside id="sidebar" class="sidebar fe-admin-sidebar" aria-label="Admin navigation">
    <a href="{{ route('dashboard') }}" class="fe-side-brand">
        <img src="{{ asset('frontend/assets/images/logos/logo-on-dark.png') }}" alt="Forward Edge Consulting" width="96" height="50">
        <small>Website admin</small>
    </a>

    <nav class="fe-nav" id="feNav">
        @foreach ($groups as $label => $items)
            <p class="fe-nav-group">{{ $label }}</p>
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}" data-group="{{ $label }}"
                   class="fe-nav-link {{ $current === $item['key'] ? 'is-current' : '' }}"
                   @if ($current === $item['key']) aria-current="page" @endif>
                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $item['label'] }}</span>
                    @if (!empty($item['badge']))
                        <span class="fe-nav-badge fe-num" aria-label="{{ $item['badge'] }} new">{{ $item['badge'] }}</span>
                    @endif
                </a>
                @if (!empty($item['children']) && $current === $item['key'])
                    @foreach ($item['children'] as $child)
                        <a href="{{ route($child['route']) }}" class="fe-nav-sub {{ $child['active'] ? 'is-current' : '' }}" @if ($child['active']) aria-current="page" @endif>{{ $child['label'] }}</a>
                    @endforeach
                @endif
            @endforeach
        @endforeach
    </nav>

    <div class="fe-side-foot">
        <div class="fe-user">
            <span class="fe-avatar" aria-hidden="true">{{ $initial }}</span>
            <span class="fe-user-text"><strong>{{ $user?->name ?? 'Admin' }}</strong><small>Administrator</small></span>
        </div>
        <div class="fe-side-actions">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="fe-side-btn">View site <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="fe-side-btn">Sign out</button>
            </form>
        </div>
    </div>
</aside>
