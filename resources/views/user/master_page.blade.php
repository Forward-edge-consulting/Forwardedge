<!doctype html>
<html class="no-js" lang="en">

<head>
    @php
        $seo = seo()->toArray();
        $pageTitle = trim($__env->yieldContent('title', $seo['title']));
    @endphp
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $seo['site_name'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:image" content="{{ $seo['image'] }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">
    <meta name="twitter:site" content="@ForwardEdgeNg">

    <!-- Site Title -->
    <title>{{ $pageTitle }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('frontend/assets/images/fav.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- CSS here -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&display=swap">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/fe-loader.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/font-awesome-pro.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bexon-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/venobox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/odometer-theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/meanmenu.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/main.css') }}">

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "Forward Edge Consulting",
      "url": "{{ config('app.url') }}",
      "logo": "{{ asset('frontend/assets/images/logos/logo.png') }}"
    }
    </script>
    @stack('jsonld')
    @if (config('services.meta_pixel.id'))
        <!-- Meta Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)n._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ config('services.meta_pixel.id') }}');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id={{ config('services.meta_pixel.id') }}&ev=PageView&noscript=1" />
        </noscript>
        <!-- End Meta Pixel Code -->
    @endif
    <style>
        .header-area .site_logo .logo img { width: clamp(120px, 12vw, 170px); height: auto; }
        .header-area .header-wrapper { gap: 24px; }
        .header-area .mainmenu > ul { gap: clamp(12px, 1.5vw, 24px); }
        .header-area .mainmenu > ul > li { margin-inline-end: 0; }
        @media (max-width: 1199px) and (min-width: 992px) { .header-right-item .header-button { display: none; } }

        .tji-box:before {
            content: "";
            display: inline-block;
            width: 24px;
            height: 24px;
            background: url('{{ asset('frontend/assets/images/fav.png') }}') no-repeat center center;
            background-size: contain;
        }

        .header-button .dropdown-menu {
            z-index: 3000 !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }

        .header-button .tj-primary-btn {
            cursor: pointer;
        }

        #toast-container>.toast {
            background-color: rgba(0, 0, 0, 0.85) !important;
            color: #fff !important;
            font-size: 14px;
            opacity: 1 !important;
        }

        #toast-container>.toast-success {
            background-color: #28a745 !important;
        }

        #toast-container>.toast-error {
            background-color: #dc3545 !important;
        }

        #toast-container>.toast-info {
            background-color: #17a2b8 !important;
        }

        #toast-container>.toast-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        /* Modal z-index fix */
        .modal {
            z-index: 9999 !important;
        }

        .modal-backdrop {
            z-index: 9998 !important;
        }

        /* Preserve Page Builder line breaks */
        /* .pb-rich-text :where(p, h1, h2, h3, h4, h5, h6, span, small, li, div, strong, em) {
            white-space: pre-line;
        } */

        /* .pb-rich-text .btn-text span,
        .pb-rich-text .badge,
        .pb-rich-text .price-number,
        .pb-rich-text .package-currency,
        .pb-rich-text .package-period,
        .pb-rich-text .number {
            white-space: normal;
        } */
    </style>
    @stack('styles')
</head>

<body @yield('body_attributes')>
    <div class="body-overlay"></div>

    <!-- Preloader Start -->
    <div class="tj-preloader is-loading" id="tj-preloader">
        <div class="tj-preloader-inner">
            <div class="tj-preloader-ball-wrap">
                <div class="tj-preloader-ball-inner-wrap">
                    <div class="tj-preloader-ball-inner">
                        <div class="tj-preloader-ball"></div>
                    </div>
                    <div class="tj-preloader-ball-shadow"></div>
                </div>
                <div id="tj-weave-anim" class="tj-preloader-text">Loading...</div>
            </div>
        </div>
        <div class="tj-preloader-overlay"></div>
    </div>
    <script>
        (function () {
            var el = document.getElementById('tj-preloader');
            function hide() {
                if (!el) return;
                el.classList.remove('is-loading');
                el.classList.add('is-loaded');
                el.style.transition = 'opacity .4s';
                el.style.opacity = '0';
                setTimeout(function () { el.style.display = 'none'; }, 450);
                el = null;
            }
            window.addEventListener('load', function () { setTimeout(hide, 300); });
            setTimeout(hide, 4000); // never block the page if an asset hangs
            window.addEventListener('pageshow', function (e) { if (e.persisted) hide(); });
        })();
    </script>
    <!-- Preloader end -->

    <!-- back to top start -->
    <div id="tj-back-to-top"><span id="tj-back-to-top-percentage"></span></div>
    <!-- back to top end -->

    <!-- start: Search Popup -->
    <div class="search-popup-overlay"></div>
    <!-- end: Search Popup -->

    <!-- start: Offcanvas Menu -->
    <div class="tj-offcanvas-area d-none d-lg-block">
        <div class="hamburger_bg"></div>
        <div class="hamburger_wrapper">
            <div class="hamburger_inner">
                <div class="hamburger_top d-flex align-items-center justify-content-between">
                    <div class="hamburger_logo">
                        <a href="index.html" class="mobile_logo">
                            <img src="{{ asset('frontend/assets/images/logos/logo.png') }}" alt="Logo">
                        </a>
                    </div>
                    <div class="hamburger_close">
                        <button class="hamburger_close_btn"><i class="fa-thin fa-times"></i></button>
                    </div>
                </div>
                <div class="offcanvas-text">
                    <p>Developing personalize our customer journeys to increase satisfaction & loyalty of our expansion
                        recognized by industry leaders.</p>
                </div>
                {{-- <div class="hamburger-search-area">
                    <h5 class="hamburger-title">Search Now!</h5>
                    <div class="hamburger_search">
                        <form method="get" action="index.html">
                            <button type="submit"><i class="tji-search"></i></button>
                            <input type="search" autocomplete="off" name="s" value=""
                                placeholder="Search here...">
                        </form>
                    </div>
                </div> --}}
                <div class="hamburger-infos">
                    <h5 class="hamburger-title">Contact Info</h5>
                    <div class="contact-info">
                        <div class="contact-item">
                            <span class="subtitle">Phone</span>
                            <a class="contact-link" href="tel:+2347039955591">+234 703 995 5591</a>
                        </div>
                        <div class="contact-item">
                            <span class="subtitle">Email</span>
                            <a class="contact-link"
                                href="mailto:info@forwardedgeconsulting.com">info@forwardedgeconsulting.com</a>
                        </div>
                        <div class="contact-item">
                            <span class="subtitle">Location</span>
                            <span class="contact-link">Iwaya Road, 58 Iwaya Rd, Yaba, Lagos State</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hamburger-socials">
                <h5 class="hamburger-title">Follow Us</h5>
                <div class="social-links style-3">
                    <ul>
                        <li><a href="https://facebook.com/forwardedgeconsulting/

" target="_blank"><i
                                    class="fa-brands fa-facebook-f"></i></a></li>
                        <li><a href="https://www.instagram.com/forwardedge_consultingltd
" target="_blank"><i
                                    class="fa-brands fa-instagram"></i></a></li>
                        <li><a href="https://x.com/ForwardEdgeNg" target="_blank"><i class="fa-brands fa-x-twitter"></i></a></li>
                        <li><a href="https://www.linkedin.com/company/forward-edge-consulting-ltd/
" target="_blank"><i
                                    class="fa-brands fa-linkedin-in"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- end: Offcanvas Menu -->

    <!-- start: Hamburger Menu -->
    <div class="hamburger-area d-lg-none">
        <div class="hamburger_bg"></div>
        <div class="hamburger_wrapper">
            <div class="hamburger_inner">
                <div class="hamburger_top d-flex align-items-center justify-content-between">
                    <div class="hamburger_logo">
                        <a href="index.html" class="mobile_logo">
                            <img src="{{ asset('frontend/assets/images/logos/logo.png') }}" alt="Logo">
                        </a>
                    </div>
                    <div class="hamburger_close">
                        <button class="hamburger_close_btn"><i class="fa-thin fa-times"></i></button>
                    </div>
                </div>
                <div class="hamburger_menu">
                    <div class="mobile_menu"></div>
                </div>
                <div class="hamburger-infos">
                    <h5 class="hamburger-title">Contact Info</h5>
                    <div class="contact-info">
                        <div class="contact-item">
                            <span class="subtitle">Phone</span>
                            <a class="contact-link" href="tel:+2347039955591">+234 703 995 5591</a>
                        </div>
                        <div class="contact-item">
                            <span class="subtitle">Email</span>
                            <a class="contact-link" href="mailto:info@forwardedgeconsulting.com">info@forwardedgeconsulting.com</a>
                        </div>
                        <div class="contact-item">
                            <span class="subtitle">Location</span>
                            <span class="contact-link">Iwaya Road, 58 Iwaya Rd, Yaba, Lagos State</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hamburger-socials">
                <h5 class="hamburger-title">Follow Us</h5>
                <div class="social-links style-3">
                    <ul>
                        <li><a href="https://facebook.com/forwardedgeconsulting/

" target="_blank"><i
                                    class="fa-brands fa-facebook-f"></i></a></li>
                        <li><a href="https://www.instagram.com/forwardedge_consultingltd
" target="_blank"><i
                                    class="fa-brands fa-instagram"></i></a></li>
                        <li><a href="https://x.com/ForwardEdgeNg" target="_blank"><i class="fa-brands fa-x-twitter"></i></a></li>
                        <li><a href="https://www.linkedin.com/company/forward-edge-consulting-ltd/
" target="_blank"><i
                                    class="fa-brands fa-linkedin-in"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- end: Hamburger Menu -->

@if (!View::hasSection('hide_header'))
    @include('user.partials.header') @endif
    <div id="smooth-wrapper">
                                <div id="smooth-content">
                                    <main id="primary" class="site-main">
                                        <div class="space-for-header"></div>

                                        @yield('main')
                                        @include('user.partials.footer')
                                    </main>
                                </div>
                        </div>


                        <!-- JS here -->
                        <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
                        <script src="{{ asset('frontend/assets/js/fe-loader.js') }}?v=1"></script>
                        <script src="{{ asset('frontend/assets/js/newsletter-forms.js') }}?v=2"></script>

                        <!-- Note: CSRF helper removed - inline syncCsrfToken() below handles it -->

                        <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/gsap.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/ScrollSmoother.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/gsap-scroll-to-plugin.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/gsap-scroll-trigger.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/gsap-split-text.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/jquery.nice-select.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/swiper.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/odometer.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/venobox.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/appear.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/wow.min.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/meanmenu.js') }}"></script>
                        <script src="{{ asset('frontend/assets/js/main.js') }}"></script>

                        <script>
                            // CSRF Token Management
                            function syncCsrfToken() {
                                // Always use meta tag token - most reliable
                                const token = $('meta[name="csrf-token"]').attr('content');

                                if (!token) {
                                    console.warn('CSRF token not found in meta tag');
                                    return;
                                }

                                // Update all token fields
                                $('input[name="_token"]').val(token);

                                // Set up jQuery AJAX to include CSRF token
                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': token
                                    }
                                });
                            }

                            // Initialize CSRF token on page load
                            syncCsrfToken();

                            // Toastr defaults
                            toastr.options = {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-top-right",
                                timeOut: "7000"
                            };

                            // Dropdown fix
                            document.addEventListener('DOMContentLoaded', function() {
                                if (typeof bootstrap === 'undefined') {
                                    console.warn('Bootstrap JS not found. Dropdowns will not work.');
                                    return;
                                }
                                document.querySelectorAll('.header-button .dropdown-toggle').forEach(function(toggle) {
                                    const menu = toggle.nextElementSibling;
                                    if (menu) menu.style.zIndex = 3000;
                                    toggle.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        const instance = bootstrap.Dropdown.getOrCreateInstance(toggle);
                                        instance.toggle();
                                    });
                                });
                            });

                        </script>


                        @stack('scripts')
</body>

</html>
