     <!-- start: Cta Section -->
     @if (!request()->routeIs('page.show', 'consultancy', 'events.*'))
     <section class="tj-cta-section">
         <div class="container">
             <div class="row">
                 <div class="col-12">
                     <div class="cta-area">
                         <div class="cta-content">
                             <h2 class="title title-anim">Let’s Build A Future Together.</h2>
                             <div class="cta-btn wow fadeInUp" data-wow-delay=".6s">
                                 <a class="tj-primary-btn btn-dark" href="{{ route('contact') }}">
                                     <span class="btn-text"><span>Get Started Now</span></span>
                                     <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                 </a>
                             </div>
                         </div>
                         <div class="cta-img">
                             <img src="{{ asset('frontend/assets/images/cta/picture2.jpg') }}" alt="">
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </section>
     @endif
     <!-- end: Cta Section -->
     <!-- start: Footer Section -->
     <footer class="tj-footer-section footer-1 section-gap-x">
         <div class="footer-main-area">
             <div class="container">
                 <div class="row justify-content-between">
                     <div class="col-xl-3 col-lg-4 col-md-6">
                         <div class="footer-widget wow fadeInUp" data-wow-delay=".1s">
                             <div class="footer-logo">
                                 <a href="{{ route('home') }}">
                                     <img src="{{ asset('frontend/assets/images/logos/logo.png') }}" alt="Logos">
                                 </a>
                             </div>
                             <div class="footer-text">
                                 <p>Information security consulting and cybersecurity training for growing businesses.</p>
                             </div>
                         </div>
                     </div>
                     <div class="col-xl-3 col-lg-4 col-md-6">
                         <div class="footer-widget widget-nav-menu wow fadeInUp" data-wow-delay=".3s">
                             <h5 class="title">Services</h5>

                             @php
                                 use App\Models\Service;

                                 // tiny query; adjust filters/columns as needed
                                 $footerServices = Service::query()
                                     ->when(
                                         Schema::hasColumn('services', 'status'),
                                         fn($q) => $q->where('status', 'published'),
                                     )
                                     ->orderByRaw(Schema::hasColumn('services', 'order') ? '"order" asc' : 'title asc')
                                     ->limit(6)
                                     ->get(['title', 'slug']);
                             @endphp

                             @if ($footerServices->isNotEmpty())
                                 <ul>
                                     @foreach ($footerServices as $svc)
                                         <li>
                                             <a href="{{ route('services.show', $svc->slug) }}">{{ $svc->title }}</a>
                                         </li>
                                     @endforeach
                                 </ul>

                                 <a class="text-btn mt-2 d-inline-flex align-items-center"
                                     href="{{ route('services') }}">
                                     <span class="btn-text"><span>View all services</span></span>
                                     <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                 </a>
                             @else
                                 <ul>
                                     <li><a href="{{ route('services') }}">Explore our services</a></li>
                                 </ul>
                             @endif
                         </div>
                     </div>
                     <div class="col-xl-2 col-lg-4 col-md-6">
                         <div class="footer-widget widget-nav-menu wow fadeInUp" data-wow-delay=".5s">
                             <h5 class="title">Resources</h5>
                             <ul>
                                 <li><a href="{{ route('about') }}">About Us</a></li>
                                 <li><a href="{{ route('services') }}">Services</a></li>
                                 <li><a href="{{ route('events.index') }}">Events</a></li>
                                 <li><a href="{{ route('gallery') }}">Gallery</a></li>
                                 <li><a href="{{ route('blog') }}">Blog</a></li>
                                 <li><a href="{{ route('contact') }}">Contact</a></li>
                             </ul>
                         </div>
                     </div>
                     <div class="col-xl-4 col-lg-5 col-md-6">
                         <div class="footer-widget widget-subscribe wow fadeInUp" data-wow-delay=".7s">
                             <h3 class="title">Subscribe to Our Newsletter.</h3>
                            <div class="subscribe-form">
                                <form class="newsletter-form js-newsletter-form"
                                    action="{{ route('newsletter.subscribe', [], false) }}"
                                    method="POST"
                                    data-block-id="footer-newsletter"
                                    data-tags='["Footer"]'>
                                    @csrf
                                    <input type="hidden" name="form_tags" value="Footer">
                                    <input type="email" name="email" placeholder="Enter email" required
                                        data-field-input
                                        data-field-label="Email"
                                        data-field-name="email"
                                        data-field-type="email"
                                        data-field-required="1">
                                    <button type="submit" aria-label="Subscribe"><i class="tji-plane"></i></button>
                                    <x-turnstile appearance="interaction-only" />
                                    <label for="footer-agree">
                                        <input id="footer-agree" type="checkbox" name="consent" value="1" required data-consent="I agree to receive emails from Forward Edge and accept the Privacy Policy.">
                                        <span>I agree to receive emails from Forward Edge and accept the <a href="{{ route('privacy') }}">Privacy Policy</a>.</span>
                                    </label>
                                    <div class="dynamic-form-feedback" role="status" aria-live="polite"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                 </div>
             </div>
         </div>
         <div class="tj-copyright-area">
             <div class="container">
                 <div class="row">
                     <div class="col-12">
                         <div class="copyright-content-area">
                             <div class="footer-contact">
                                 <ul>
                                     <li>
                                         <a href="tel:{{ config('contact.phone_link') }}">
                                             <span class="icon"><i class="tji-phone-2"></i></span>
                                             <span class="text">+234 703 995 5591</span>
                                         </a>
                                     </li>
                                     <li>
                                         <a href="mailto:{{ config('contact.email') }}">
                                             <span class="icon"><i class="tji-envelop-2"></i></span>
                                             <span class="text">info@forwardedgeconsulting.com</span>
                                         </a>
                                     </li>
                                 </ul>
                             </div>
                             <div class="social-links">
                                 <ul>
                                     <li><a href="https://facebook.com/forwardedgeconsulting/

" target="_blank"><i
                                                 class="fa-brands fa-facebook-f"></i></a>
                                     </li>
                                     <li><a href="https://www.instagram.com/forwardedge_consultingltd
" target="_blank"><i
                                                 class="fa-brands fa-instagram"></i></a>
                                     </li>
                                     <li><a href="https://x.com/ForwardEdgeNg" target="_blank"><i
                                                 class="fa-brands fa-x-twitter"></i></a></li>
                                     <li><a href="https://www.linkedin.com/company/forward-edge-consulting-ltd/
" target="_blank"><i
                                                 class="fa-brands fa-linkedin-in"></i></a>
                                     </li>
                                 </ul>
                             </div>
                             <div class="footer-legal-links">
                                 <ul>
                                     <li><a href="{{ route('terms') }}">Terms & Conditions</a></li>
                                     <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                                 </ul>
                             </div>
                             <div class="copyright-text">
                                 <p>&copy; {{ date('Y') }} Forward Edge Consulting. All rights reserved.</p>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         <div class="bg-shape-1">
             <img src="{{ asset('frontend/assets/images/shape/pattern-2.svg') }}" alt="">
         </div>
         <div class="bg-shape-2">
             <img src="{{ asset('frontend/assets/images/shape/pattern-3.svg') }}" alt="">
         </div>
     </footer>
     <!-- end: Footer Section -->
