<header id="header" class="header fixed-top fe-admin-header">
    <div class="fe-header-inner">
        <button class="fe-icon-btn toggle-sidebar-btn" type="button" aria-label="Toggle navigation">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="fe-brand d-xl-none">
            <img src="{{ asset('frontend/assets/images/logos/logo.png') }}" alt="Forward Edge Consulting" width="62" height="32">
        </a>

        <button type="button" class="fe-jump" id="feJump" aria-haspopup="dialog" aria-label="Search or jump to a page">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span>Search or jump to…</span>
            <kbd class="fe-kbd">Ctrl K</kbd>
        </button>

        <div class="fe-header-actions">
            <div class="dropdown">
                <button class="btn btn-primary fe-new" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i><span>New</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end fe-new-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.events.create') }}"><i class="bi bi-calendar-event" aria-hidden="true"></i>Event</a></li>
                    <li>
                        <form method="POST" action="{{ route('admin.blogs.store') }}" class="m-0">@csrf
                            <button class="dropdown-item" type="submit"><i class="bi bi-journal-text" aria-hidden="true"></i>Blog post</button>
                        </form>
                    </li>
                    <li><a class="dropdown-item" href="{{ route('pb.pages.create') }}"><i class="bi bi-file-earmark-richtext" aria-hidden="true"></i>Page</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.forms.create') }}"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i>Form</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.services.add') }}"><i class="bi bi-briefcase" aria-hidden="true"></i>Service</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>
