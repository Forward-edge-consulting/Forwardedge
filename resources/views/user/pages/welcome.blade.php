@extends('user.master_page')
@section('title', ' Welcome | Forward Edge Consulting')
@push('styles')
    <style>
        .fe-hero-photo img { object-position: 28% center; }
        .fe-hero-photo::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: rgba(14, 77, 118, .38);
            pointer-events: none;
        }
        .fe-home-consultancy { display:inline-flex; align-items:center; gap:12px; margin-top:24px; padding:12px 20px; border:0; border-radius:6px; min-height:48px; background:#087eaf; color:#fff; font-weight:600; font-size:16px; line-height:1.5; }
        .fe-home-consultancy:hover { color:#fff; background:#066891; }
        .fe-home-consultancy:focus-visible { outline:2px solid #fff; outline-offset:4px; }
        .tj-about-section .about-content-area { min-height:0; padding:clamp(24px,4vw,48px); }
        .fe-about-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:24px; align-items:stretch; }
        .tj-about-section .about-content-area { height:100%; }
        .tj-about-section .about-bottom-area { margin:0; display:flex; }
        .tj-about-section .client-review-cont { width:100%; max-width:none; display:flex; align-items:center; padding:clamp(24px,4vw,48px); }
        .tj-about-section .client-review-cont .desc { margin:0; font-size:18px; line-height:1.7; }
        @media(max-width:991px) { .fe-about-grid { grid-template-columns:1fr; } }
    </style>
@endpush
@section('main')

    <!-- start: Banner Section -->
    <section class="tj-banner-section section-gap-x">
        <div class="banner-area">
            <div class="banner-left-box">
                <div class="banner-content">
                    <span class="sub-title wow fadeInDown" data-wow-delay=".2s">
                        <i class="tji-excellence"></i> Recognized for Excellence
                    </span>
                    <h1 class="banner-title title-anim">Where vision meets
                        <span>Direction.</span>
                    </h1>
                    <div class="banner-desc-area wow fadeInUp" data-wow-delay=".7s">
                        <a class="banner-link" href="{{ route('about') }}" aria-label="About Forward Edge">
                            <span><i class="tji-arrow-right-big"></i></span>
                        </a>
                        <div class="banner-desc">Cybersecurity, IT & Renewable Energy Solutions for a Secure and Resilient
                            Future.
                        </div>
                    </div>
                    <a class="fe-home-consultancy" href="{{ route('consultancy') }}">Book a consultancy <span aria-hidden="true">↗</span></a>
                </div>
                <div class="banner-shape">
                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/shape/pattern-bg.webp') }}" alt="">
                </div>
            </div>
            <div class="banner-right-box">
                @include('user.partials.hero-slideshow')
            </div>
        </div>
        <div class="banner-scroll wow fadeInDown" data-wow-delay="2s">
            <a href="#choose" class="scroll-down">
                <span><i class="tji-arrow-down-long"></i></span>
                Scroll Down
            </a>
        </div>
    </section>
    <!-- end: Banner Section -->

    <!-- start: Choose Section -->
    <section id="choose" class="tj-choose-section section-gap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="sec-heading text-center">
                        <span class="sub-title wow fadeInUp" data-wow-delay=".3s"><i class="tji-box"></i>Choose the
                            Best</span>
                        <h2 class="sec-title title-anim">Empowering Business with <span>Expertise.</span>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="row row-gap-4 rightSwipeWrap">
                <div class="col-lg-4">
                    <div class="choose-box right-swipe">
                        <div class="choose-content">
                            <div class="choose-icon">
                                <i class="tji-innovative"></i>
                            </div>
                            <h4 class="title">Innovative Solutions</h4>
                            <p class="desc">We stay ahead of the curve, leveraging cutting-edge
                                technologies and strategies to
                                keep
                                you competitive in a marketplace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="choose-box right-swipe">
                        <div class="choose-content">
                            <div class="choose-icon">
                                <i class="tji-award"></i>
                            </div>
                            <h4 class="title">Award-Winning Expertise</h4>
                            <p class="desc">Recognized by industry leaders, our award-winning team has a
                                proven record of
                                delivering
                                excellence across projects.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="choose-box right-swipe">
                        <div class="choose-content">
                            <div class="choose-icon">
                                <i class="tji-support"></i>
                            </div>
                            <h4 class="title">Dedicated Support</h4>
                            <p class="desc">Our team is always available to address your concerns,
                                providing quick and effective
                                solution to keep your business.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end: Choose Section -->

    <!-- start: Client Section -->
    <section class="tj-client-section client-section-gap wow fadeInUp" data-wow-delay=".4s">
        <div class="container-fluid client-container">
            <div class="row">
                <div class="col-12">
                    <div class="client-content">
                        <h5 class="sec-title"> <span class="client-numbers">10+</span> Companies
                            affiliated with
                            <span class="client-text">Forward Edge</span> Here
                        </h5>
                    </div>
                    <div class="swiper client-slider client-slider-1">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide client-item">
                                <div class="clien">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download1.png') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download2.png') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download3.png') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/downloa4.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download5.png') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download6.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download7.png') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download8.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download9.png') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download10.png') }}"
                                        alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/skyhigh11.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/download12.png') }}"
                                        alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/client7.jpg') }}" alt="">
                                </div>
                            </div><div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/client8.jpg') }}" alt="">
                                </div>
                            </div><div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/client9.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/client10.png') }}" alt="">
                                </div>
                            </div><div class="swiper-slide client-item">
                                <div class="">
                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/brands/client11.jpg') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end: Client Section -->

    <!-- start: About Section -->
    <section class="tj-about-section section-gap">
        <div class="container">
            <div class="row">
                <div class="col-12 fe-about-grid">
                    <div class="about-content-area style-1 wow fadeInLeft" data-wow-delay=".2s">
                        <div class="sec-heading">
                            <span class="sub-title wow fadeInUp" data-wow-delay=".3s"><i class="tji-box"></i>Get to
                                Know
                                Us</span>
                            <h2 class="sec-title title-anim">Technology and expertise for
                                <span>stronger businesses.</span>
                            </h2>
                        </div>
                        <div class="wow fadeInUp" data-wow-delay=".5s">
                            <a class="text-btn" href="{{ route('about') }}">
                                <span class="btn-text"><span>Learn More</span></span>
                                <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                            </a>
                        </div>
                    </div>
                    <div class="about-bottom-area">
                        <div class="client-review-cont wow fadeInUp" data-wow-delay=".7s">
                            <p class="desc"> Forward Edge Consulting is a technology and security consulting firm helping
                                organizations across Africa build resilience through cybersecurity, cloud solutions, AI
                                integration, and enterprise IT infrastructure. In addition to our digital transformation
                                services, we now provide renewable energy solutions — including solar and inverter
                                installations — empowering businesses and communities to achieve both digital and energy
                                resilience.
                            </p>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end: About Section -->

    <!-- start: Service Section -->
    <section class="tj-service-section overflow-hidden section-gap section-gap-x">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="sec-heading text-center">
                        <span class="sub-title text-white wow fadeInUp" data-wow-delay=".3s"><i class="tji-box"></i>Our
                            Solutions</span>
                        <h2 class="sec-title text-white title-anim">Solutions to Transform Your
                            <span>Business.</span>
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-12">
                    <div class="service-wrapper wow fadeInUp" data-wow-delay=".4s">
                        <div class="swiper service-slider">
                            <div class="swiper-wrapper">

                                @foreach ($services as $service)
                                    <div class="swiper-slide">
                                        <div class="service-item style-1">
                                            <div class="service-img">
                                                <img loading="lazy" decoding="async" src="{{ asset('storage/' . $service->thumbnail) }}"
                                                    alt="{{ $service->title }}">
                                            </div>
                                            <div class="service-icon"> <i class="tji-service-1"></i> </div>
                                            <div class="service-content">
                                                <h4 class="title">
                                                    <a href="{{ route('services.show', $service->slug) }}">
                                                        {{ $service->title }}
                                                    </a>
                                                </h4>
                                                <p class="desc">
                                                    {{ Str::limit($service->brief_description, 180) }}
                                                </p>
                                                <a class="text-btn" href="{{ route('services.show', $service->slug) }}">
                                                    <span class="btn-text"><span>Learn More</span></span>
                                                    <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="swiper-pagination-area white-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-shape-1">
            <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/shape/pattern-2.svg') }}" alt="">
        </div>
        <div class="bg-shape-2">
            <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/shape/pattern-3.svg') }}" alt="">
        </div>
    </section>
    <!-- end: Service Section -->

    <!-- start: Project Section -->
    <section class="tj-project-section section-gap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="sec-heading-wrap">
                        <span class="sub-title wow fadeInUp" data-wow-delay=".3s"><i class="tji-box"></i>Proud
                            Projects</span>
                        <div class="heading-wrap-content">
                            <div class="sec-heading">
                                <h2 class="sec-title title-anim">Driving Impact Through Real
                                    <span>Projects.</span>
                                </h2>
                            </div>
                            <p class="desc wow fadeInUp" data-wow-delay=".5s">Every project we deliver tells a story of
                                resilience, transformation, and progress. From strengthening organizations against cyber
                                threats to deploying renewable energy solutions and enabling digital transformation, we are
                                proud to create lasting value for our clients and communities.</p>
                            {{-- <div class="btn-wrap wow fadeInUp" data-wow-delay=".6s">
                                <a class="tj-primary-btn" href="{{ route('gallery') }}">
                                    <span class="btn-text"><span>More Projects</span></span>
                                    <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                </a>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="project-area tj-arrange-container">
                        <div class="project-item tj-arrange-item">
                            <div class="project-img"
                                data-bg-image="{{ asset('frontend/assets/images/project/pic1.jpg') }}"></div>
                            <div class="project-content">
                                <span class="categories">Connect</span>
                                <div class="project-text">
                                    <h4 class="title"><a href="">Building Cyber Resilience </a></h4>
                                    <p class="desc" style="color:white;">Helping organizations protect critical assets
                                        and stay ahead of evolving threats.</p>
                                    <a class="project-btn" href="">
                                        <i class="tji-arrow-right-long"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="project-item tj-arrange-item">
                            <div class="project-img"
                                data-bg-image="{{ asset('frontend/assets/images/project/pic2.jpg') }}"></div>
                            <div class="project-content">
                                <span class="categories"><a href="">Empower</a></span>
                                <div class="project-text">
                                    <h4 class="title"><a href="">Developing the Cyber Workforce </a></h4>
                                    <p class="desc" style="color:white;">Equipping professionals and teams with hands-on
                                        training and compliance skills.
                                    </p>
                                    <a class="project-btn" href="">
                                        <i class="tji-arrow-right-long"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="project-item tj-arrange-item">
                            <div class="project-img"
                                data-bg-image="{{ asset('frontend/assets/images/project/picture3.jpg') }}"></div>
                            <div class="project-content">
                                <span class="categories"><a href="">Support</a></span>
                                <div class="project-text">
                                    <h4 class="title"><a href="">Enabling Digital Transformation </a></h4>
                                    <p class="desc" style="color:white;">Delivering secure IT, infrastructure, and
                                        technology solutions that drive efficiency.</p>
                                    <a class="project-btn" href="">
                                        <i class="tji-arrow-right-long"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="project-item tj-arrange-item">
                            <div class="project-img"
                                data-bg-image="{{ asset('frontend/assets/images/project/solar.jpg') }}"></div>
                            <div class="project-content">
                                <span class="categories"><a href="">Business</a></span>
                                <div class="project-text">
                                    <h4 class="title"><a href="">Expanding Renewable Energy Access</a></h4>
                                    <p class="desc" style="color:white;">Providing solar and inverter systems that
                                        ensure reliable and sustainable power.</p>
                                    <a class="project-btn" href="">
                                        <i class="tji-arrow-right-long"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end: Project Section -->

    <!-- start: Countup Section -->
    <div class="tj-countup-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="countup-wrap">
                        <div class="countup-item">
                            <div class="inline-content">
                                <span class="odometer countup-number" data-count="93"></span>
                                <span class="count-plus">%</span>
                            </div>
                            <span class="count-text">Projects Completed.</span>
                            <span class="count-separator"
                                data-bg-image="{{ asset('frontend/assets/images/shape/separator.svg') }}"></span>
                        </div>
                        <div class="countup-item">
                            <div class="inline-content">
                                <span class="odometer countup-number" data-count="20"></span>
                                <span class="count-plus">k</span>
                            </div>
                            <span class="count-text">Reach Worldwide</span>
                            <span class="count-separator"
                                data-bg-image="{{ asset('frontend/assets/images/shape/separator.svg') }}"></span>
                        </div>
                        <div class="countup-item">
                            <div class="inline-content">
                                <span class="odometer countup-number" data-count="8.5"></span>
                                <span class="count-plus">X</span>
                            </div>
                            <span class="count-text">Faster Growth</span>
                            <span class="count-separator"
                                data-bg-image="{{ asset('frontend/assets/images/shape/separator.svg') }}"></span>
                        </div>
                        <div class="countup-item">
                            <div class="inline-content">
                                <span class="odometer countup-number" data-count="10"></span>
                                <span class="count-plus">+</span>
                            </div>
                            <span class="count-text">Awards Achieved</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end: Countup Section -->

    <!-- start: Testimonial Section -->
    <section class="tj-testimonial-section section-gap section-gap-x">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-12">
                    <div class="sec-heading-wrap">
                        <span class="sub-title wow fadeInUp" data-wow-delay=".3s"><i class="tji-box"></i>Clients
                            Feedback</span>
                        <div class="heading-wrap-content">
                            <div class="sec-heading">
                                <h2 class="sec-title title-anim">Success <span>Stories</span> Fuel our
                                    Innovation.</h2>
                            </div>
                            <div class="slider-navigation d-inline-flex wow fadeInUp" data-wow-delay=".4s">
                                <div class="slider-prev">
                                    <span class="anim-icon">
                                        <i class="tji-arrow-left"></i>
                                        <i class="tji-arrow-left"></i>
                                    </span>
                                </div>
                                <div class="slider-next">
                                    <span class="anim-icon">
                                        <i class="tji-arrow-right"></i>
                                        <i class="tji-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="testimonial-wrapper wow fadeInUp" data-wow-delay=".5s">
                        <div class="swiper swiper-container testimonial-slider">
                            <div class="swiper-wrapper d-flex align-items-stretch"> <!-- flex here -->

                                <!-- Testimonial 1: Cybersecurity & Compliance -->
                                <div class="swiper-slide d-flex">
                                    <div
                                        class="testimonial-item d-flex flex-column justify-content-between h-100 p-4 border border-info rounded-lg shadow-xl bg-gray-900 text-white">
                                        <span class="quote-icon text-info"><i class="tji-quote"></i></span>
                                        <div class="flex-grow-1 mb-3">
                                            <p class="desc" style="color:black;">ForwardEdge completely transformed our security posture from reactive to
                                                proactive. Their ZTA implementation and penetration testing uncovered gaps
                                                we didn't know existed. We now have a truly resilient system integrated with
                                                SIEM/SOAR, giving us confidence in our compliance and defense against
                                                advanced threats.</p>
                                        </div>
                                        <div class="testimonial-author mt-auto">
                                            <div class="author-inner d-flex align-items-center">
                                                <div class="author-img me-2">
                                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/testimonial/client1.jpeg') }}"
                                                        alt="Client 1">
                                                </div>
                                                <div class="author-header">
                                                    <h4 class="title mb-0">Joshua Odubu</h4>
                                                    {{-- <span class="designation text-secondary">CTO, FinTech
                                                        Innovations</span> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Testimonial 2: Cloud & FinOps -->
                                <div class="swiper-slide d-flex">
                                    <div
                                        class="testimonial-item d-flex flex-column justify-content-between h-100 p-4 border border-info rounded-lg shadow-xl bg-gray-900 text-white">
                                        <span class="quote-icon text-info"><i class="tji-quote"></i></span>
                                        <div class="desc flex-grow-1 mb-3">
                                            <p style="color:black;">We challenged ForwardEdge to optimize our massive cloud spend. Their team
                                                didn't just migrate us; they implemented a rigorous FinOps and IaC strategy,
                                                resulting in a 35% reduction in monthly cloud costs within six months.
                                                The performance improvements and stability provided by their governance
                                                model were equally impactful.</p>
                                        </div>
                                        <div class="testimonial-author mt-auto">
                                            <div class="author-inner d-flex align-items-center">
                                                <div class="author-img me-2">
                                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/testimonial/client2.jpeg') }}"
                                                        alt="Client 2">
                                                </div>
                                                <div class="author-header">
                                                    <h4 class="title mb-0">Chris Davies</h4>
                                                    {{-- <span class="designation text-secondary">VP of Infrastructure, Global
                                                        Manufacturing</span> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Testimonial 3: Software Development (Matching your selected text) -->
                                <div class="swiper-slide d-flex">
                                    <div
                                        class="testimonial-item d-flex flex-column justify-content-between h-100 p-4 border border-info rounded-lg shadow-xl bg-gray-900 text-white">
                                        <span class="quote-icon text-info"><i class="tji-quote"></i></span>
                                        <div class="desc flex-grow-1 mb-3">
                                            <p style="color:black;">The team’s expertise in Domain-Driven Design and Microservices was
                                                clear from the start. They delivered a complex platform on time using their
                                                hyper-transparent Agile process. The resulting product is incredibly
                                                scalable and our in-house team is now better equipped, thanks to their focus
                                                on High-Performance Engineering.</p>
                                        </div>
                                        <div class="testimonial-author mt-auto">
                                            <div class="author-inner d-flex align-items-center">
                                                <div class="author-img me-2">
                                                    <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/testimonial/client3.jpeg') }}"
                                                        alt="Client 3">
                                                </div>
                                                <div class="author-header">
                                                    <h4 class="title mb-0">Ebuwa Ahusimer</h4>
                                                    <span class="designation text-secondary">Petrokrafte Nigeria</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="swiper-pagination-area"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-shape-1">
            <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/shape/pattern-2.svg') }}" alt="">
        </div>
        <div class="bg-shape-2">
            <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/shape/pattern-3.svg') }}" alt="">
        </div>
    </section>
    <!-- end: Testimonial Section -->

    <!-- start: Faq Section -->
    <section class="tj-faq-section section-gap tj-arrange-container-2">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-lg-6">
                    <div class="faq-img-area tj-arrange-item-2">
                        <div class="faq-img overflow-hidden">
                            <img loading="lazy" decoding="async" src="{{ asset('frontend/assets/images/faq/pic.jpg') }}" alt="">
                            <h2 class="title">Need Help? Start Here...</h2>
                        </div>
                        <div class="box-area ">
                            <div class="call-box">
                                <h4 class="title">Get Started Free Call? </h4>
                                <span class="call-icon"><i class="tji-phone"></i></span>
                                <a class="number" href="tel:+2347039955591"><span>+2347039955591</span></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="accordion tj-faq tj-arrange-item-2" id="faqOne">
                        @if ($faqs->count())
                            @foreach ($faqs as $index => $faq)
                                <div class="accordion-item {{ $index === 0 ? 'active' : '' }}">
                                    <button class="faq-title {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq-{{ $index + 1 }}"
                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                        {{ $faq->question }}
                                    </button>
                                    <div id="faq-{{ $index + 1 }}" class="collapse {{ $index === 0 ? 'show' : '' }}"
                                        data-bs-parent="#faqOne">
                                        <div class="accordion-body faq-text">
                                            {{ $faq->answer }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @php
                                $dummyFaqs = [
                                    [
                                        'question' => 'What services does Bexon offer to clients?',
                                        'answer' =>
                                            'Getting started is easy! Simply email us or give us a call, and we’ll schedule a consultation to discuss your project and how we can best assist you. Our team keeps you informed throughout the process, ensuring quality control and timely delivery.',
                                    ],
                                    [
                                        'question' => 'How do I get started with Corporate Business?',
                                        'answer' =>
                                            'Getting started is easy! Simply email us or give us a call, and we’ll schedule a consultation to discuss your project and how we can best assist you.',
                                    ],
                                    [
                                        'question' => 'How do you ensure the success of a project?',
                                        'answer' =>
                                            'We ensure success by detailed planning, constant updates, and keeping you informed throughout the process.',
                                    ],
                                    [
                                        'question' => 'How long will it take to complete my project?',
                                        'answer' =>
                                            'The timeline depends on project scope, but we provide realistic estimates and regular updates.',
                                    ],
                                    [
                                        'question' => 'Can I track the progress of my project?',
                                        'answer' =>
                                            'Yes, our team provides progress reports and status updates so you are always informed.',
                                    ],
                                ];
                            @endphp

                            @foreach ($dummyFaqs as $index => $faq)
                                <div class="accordion-item {{ $index === 0 ? 'active' : '' }}">
                                    <button class="faq-title {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq-dummy-{{ $index + 1 }}"
                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                        {{ $faq['question'] }}
                                    </button>
                                    <div id="faq-dummy-{{ $index + 1 }}"
                                        class="collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#faqOne">
                                        <div class="accordion-body faq-text">
                                            {{ $faq['answer'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- end: Faq Section -->

    @include('user.partials.contact-details')

    <!-- start: Blog Section -->
    <!-- start: Blog Section -->
    <!-- start: Blog Section -->
    <section class="tj-blog-section section-gap">
        <div class="container">
            {{-- Heading --}}
            <div class="row">
                <div class="col-12">
                    <div class="sec-heading text-center">
                        <span class="sub-title wow fadeInUp" data-wow-delay=".3s">
                            <i class="tji-box"></i>Events & Insights & Ideas
                        </span>
                        <h2 class="sec-title">The Ultimate <span>Resource.</span></h2>
                    </div>
                </div>
            </div>

            @php
                $cards = collect($homeResourceCards ?? [])->take(6);
            @endphp

<div class="row row-gap-4 mt-4">
    @if ($cards->isEmpty())
        {{-- EMPTY STATE --}}
        <div class="col-xl-4 col-md-6 mx-auto">
            <div class="blog-item wow fadeInUp" data-wow-delay=".4s" style="height:100%;">
                <div class="blog-thumb d-flex align-items-center justify-content-center"
                     style="height:250px;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);">
                    {{-- your SVG empty state --}}
                    <span class="text-muted">New events or bootcamps coming soon</span>
                </div>
                <div class="blog-content text-center">
                    <h4 class="title mb-1">Stay tuned</h4>
                    <p class="m-0">We’re lining up the next batch of schedules for you.</p>
                </div>
            </div>
        </div>
    @else
        {{-- unified row of event-style cards --}}
        @foreach ($cards as $i => $c)
            <div class="col-xl-4 col-md-6">
                <div class="blog-item wow fadeInUp" data-wow-delay=".4s">
                    <div class="blog-thumb">
                        <a href="{{ $c['url'] }}">
                            <img loading="lazy" decoding="async" src="{{ $c['img'] }}" alt="{{ $c['title'] }}"
                                 style="height: 250px; width: 100%; object-fit: cover; border-radius: 8px;">
                        </a>
                        @if($c['date'] && $c['month'])
                            <div class="blog-date">
                                <span class="date">{{ $c['date'] }}</span>
                                <span class="month">{{ $c['month'] }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="categories"><a href="#">{{ $c['badge'] }}</a></span>
                            <span>{{ $c['meta_right'] }}</span>
                        </div>
                        <h4 class="title">
                            <a href="{{ $c['url'] }}">{{ $c['title'] }}</a>
                        </h4>
                        <a class="text-btn" href="{{ $c['url'] }}">
                            <span class="btn-text"><span>{{ $c['cta'] }}</span></span>
                            <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>


        </div>
    </section>

    <!-- end: Blog Section -->

    <!-- end: Blog Section -->



    <!-- end: Blog Section -->


@endsection
