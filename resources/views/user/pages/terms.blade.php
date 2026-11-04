@extends('user.master_page')
@section('title', 'Terms & Conditions | Forward Edge Consulting')
@section('breadcrumb_text', 'Understand the guidelines that govern the use of our digital products, services, and learning experiences.')

@section('main')
    @include('user.partials.breadcrumb')

    <section class="section-gap tj-legal-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="mb-5">
                        <p class="text-muted mb-1">Last updated: October 07, 2025</p>
                        <h2 class="fw-bold mb-3">Forward Edge Consulting – Terms & Conditions</h2>
                        <p class="lead text-muted">
                            These terms explain how you may access, use, and engage with Forward Edge Consulting Ltd.’s
                            websites, training portals, events, and digital services. By using our platforms you agree to
                            these rules and to follow any additional policies referenced here.
                        </p>
                    </div>

                    <div class="legal-card border rounded-4 shadow-sm p-4 p-md-5 mb-4 bg-white">
                        <h3 class="h4 mb-3">1. Using our websites & platforms</h3>
                        <p class="mb-3">
                            Provide accurate information when contacting us or registering for events, and only use our content for
                            lawful internal business or learning purposes. We may suspend or restrict access if suspicious or
                            abusive activity is detected.
                        </p>
                        <ul class="ps-3">
                            <li>Do not copy, reverse engineer, or resell our content without written consent.</li>
                            <li>Respect intellectual property notices on reports, templates, and media assets.</li>
                            <li>Any third-party software or integrations remain subject to the vendor’s licensing rules.</li>
                        </ul>
                    </div>

                    <div class="legal-card border rounded-4 shadow-sm p-4 p-md-5 mb-4 bg-white">
                        <h3 class="h4 mb-3">2. Event bookings & service agreements</h3>
                        <p class="mb-3">
                            Pricing, availability, and timelines are shared at the point of registration. Your seat is confirmed
                            after payment or a signed agreement. Unless otherwise stated, fees are non-refundable once a cohort
                            or project has started.
                        </p>
                        <p class="mb-0">We reserve the right to reschedule sessions, change facilitators, or update curricula to keep the learning experience relevant.</p>
                    </div>

                    <div class="legal-card border rounded-4 shadow-sm p-4 p-md-5 mb-4 bg-white">
                        <h3 class="h4 mb-3">3. Communication & community standards</h3>
                        <p class="mb-3">
                            Be respectful when collaborating with trainers, staff, or other participants. Harassment, hate
                            speech, or attempts to gain unauthorized access to internal assets is prohibited.
                        </p>
                        <p class="mb-0">
                            We may remove community access or end a contract if behaviour violates these standards or Nigerian
                            law.
                        </p>
                    </div>

                    <div class="legal-card border rounded-4 shadow-sm p-4 p-md-5 mb-4 bg-white">
                        <h3 class="h4 mb-3">4. Warranties & liability</h3>
                        <p class="mb-3">
                            All services are provided “as is.” While we work hard to maintain uptime and accurate information,
                            we do not guarantee uninterrupted service or particular business outcomes.
                        </p>
                        <p class="mb-0">
                            To the extent permitted by law, Forward Edge Consulting is not liable for indirect damages or loss
                            of profits resulting from your use of our products, portals, or advisory services.
                        </p>
                    </div>

                    <div class="legal-card border rounded-4 shadow-sm p-4 p-md-5 mb-4 bg-white">
                        <h3 class="h4 mb-3">5. Updates</h3>
                        <p class="mb-3">
                            We occasionally update these terms. Major changes will be announced via email or platform notice.
                            Continued use of our services after an update means you agree to the revised version.
                        </p>
                        <p class="mb-0">Questions? Reach us at <a href="mailto:info@forwardedgeconsulting.com">info@forwardedgeconsulting.com</a> or call +234&nbsp;703&nbsp;995&nbsp;5591.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
