@php
    $sections = View::getSections();
    $fullTitle = trim($sections['title'] ?? 'Page');
    $pageTitle = explode('|', $fullTitle)[0];
    $pageTitle = trim($pageTitle);
    $pageText = trim($sections['breadcrumb_text'] ?? '');

    // Map page titles to background images
    $bgImages = [
        'About Us' => 'pic4.jpg',
        'Services' => 'picture4.jpg',
        'Events and Training' => 'event1.jpg',
        'Gallery' => 'gallery.jpg',
        'Blog' => 'blog.jpg',
        'Contact' => 'contact.jpg',
        'Service Details'=>'pic3.jpg',
        'Event Registration'=>'event2.jpg'
    ];

    // Default fallback if not found
    $bgImage = $bgImages[$pageTitle] ?? 'default-bg.webp';
@endphp

     <section class="tj-page-header section-gap-x" data-bg-image="{{ asset('frontend/assets/images/bg/' . $bgImage) }}">
          <div class="container">
            <div class="row">
              <div class="col-lg-12">
                <div class="tj-page-header-content text-center">
                  <h1 class="tj-page-title">{{ $pageTitle }}</h1>
                  @if(!empty($pageText))
                      <p class="lead text-white-50 mb-4">{{ $pageText }}</p>
                  @endif
                  <div class="tj-page-link">
                    <span><i class="tji-home"></i></span>
                    <span>
                      <a href="{{ route('home') }}">Home</a>
                    </span>
                    <span><i class="tji-arrow-right"></i></span>
                    <span>
                      <span>{{ $pageTitle }}</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="page-header-overlay" data-bg-image="{{asset('frontend/assets/images/shape/pheader-overlay.webp')}}"></div>
        </section>
