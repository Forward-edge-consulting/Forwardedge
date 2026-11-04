@extends('user.master_page')
@section('title', 'Blog | Forward Edge Consulting')
@section('main')
    @include('user.partials.breadcrumb')
    <section class="tj-blog-section section-gap slidebar-stickiy-container">
        <div class="container">
            <div class="row row-gap-5">
                <div class="col-lg-8">
                    <div class="blog-post-wrapper">
                        @php use Illuminate\Support\Str; @endphp
                        @forelse ($blogs as $blog)
                            <article class="blog-item wow fadeInUp" data-wow-delay=".{{ $loop->iteration }}s">
                                <div class="blog-thumb">
                                    <a href="{{ route('blogs.show', $blog->slug) }}">
                                        <img src="{{ $blog->thumbnail ? asset('storage/' . $blog->thumbnail) : asset('frontend/assets/images/blog/blog-1.webp') }}"
                                            alt="{{ $blog->title }}" class="img-fluid"
                                            style="height: 250px; width: 100%; object-fit: cover; border-radius: 8px;">
                                    </a>
                                    <div class="blog-date">
                                        <span class="date">{{ $blog->created_at->format('d') }}</span>
                                        <span class="month">{{ $blog->created_at->format('M') }}</span>
                                    </div>
                                </div>
                                <div class="blog-content">
                                    <div class="blog-meta">
                                        <span class="categories">
                                            <a href="#">{{ $blog->category ?? 'Uncategorized' }}</a>
                                        </span>
                                        <span>By <a href="#">{{ $blog->author->name ?? 'Admin' }}</a></span>
                                    </div>
                                    <h3 class="title">
                                        <a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a>
                                    </h3>
                                    @php
                                        $firstParagraph = optional($blog->details)->sortBy('order')->firstWhere('type', 'paragraph');
                                        $excerpt = $firstParagraph ? Str::limit(strip_tags($firstParagraph->contentString()), 160) : null;
                                    @endphp
                                    @if ($excerpt)
                                        <p class="text-muted">{{ $excerpt }}</p>
                                    @endif

                                    @if ($blog->details->isEmpty())
                                        <a class="text-btn disabled" href="javascript:void(0)"
                                            style="opacity: 0.6; cursor: not-allowed;">
                                            <span class="btn-text"><span>No Details Yet</span></span>
                                            <span class="btn-icon"><i class="tji-lock"></i></span>
                                        </a>
                                    @else
                                        <a class="text-btn" href="{{ route('blogs.show', $blog->slug) }}">
                                            <span class="btn-text"><span>Read More</span></span>
                                            <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                        </a>
                                    @endif

                                </div>
                            </article>
                        @empty
                            <div class="empty-wrap my-5">
                                <!-- BLOG SVG -->
                                <svg class="empty-svg" viewBox="0 0 400 260" role="img"
                                    aria-label="Blog posts coming soon">
                                    <defs>
                                        <linearGradient id="gb3" x1="0" y1="0" x2="1"
                                            y2="1">
                                            <stop offset="0%" stop-color="#FDB714" />
                                            <stop offset="100%" stop-color="#2c99d4" />
                                        </linearGradient>
                                        <linearGradient id="gb3soft" x1="0" y1="0" x2="1"
                                            y2="1">
                                            <stop offset="0%" stop-color="#FDB714" stop-opacity=".15" />
                                            <stop offset="100%" stop-color="#2c99d4" stop-opacity=".15" />
                                        </linearGradient>
                                    </defs>
                                    <ellipse cx="200" cy="170" rx="160" ry="70"
                                        fill="url(#gb3soft)" />
                                    <!-- notebook -->
                                    <rect x="110" y="60" width="180" height="120" rx="10" fill="#fff"
                                        stroke="url(#gb3)" stroke-width="3" />
                                    <line x1="130" y1="90" x2="270" y2="90" stroke="#2c99d4"
                                        stroke-width="2" opacity=".5" />
                                    <line x1="130" y1="110" x2="240" y2="110" stroke="#2c99d4"
                                        stroke-width="2" opacity=".5" />
                                    <line x1="130" y1="130" x2="250" y2="130" stroke="#2c99d4"
                                        stroke-width="2" opacity=".5" />
                                    <!-- pen -->
                                    <path d="M265 145l22 22-16 5-6-6z" fill="#FDB714" stroke="#c27e00" stroke-width="1.5" />
                                    <circle cx="280" cy="160" r="4" fill="#2c99d4" />
                                    <text x="200" y="210" text-anchor="middle" font-family="Inter, ui-sans-serif"
                                        font-size="18" fill="#222">
                                        Blog posts coming soon
                                    </text>
                                </svg>
                                <p class="empty-text">We’re writing something great. Stay tuned!</p>
                                <a href="{{ route('blog') }}" class="btn btn-gradient">Refresh</a>
                            </div>
                        @endforelse


                        {{-- Pagination --}}
                        <div class="tj-pagination d-flex">
                            {{ $blogs->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="tj-main-sidebar slidebar-stickiy">

                        <div class="tj-sidebar-widget tj-recent-posts wow fadeInUp" data-wow-delay=".3s">
                            <h4 class="widget-title">Latest Posts</h4>
                            <ul>
                                @foreach ($latestPosts as $post)
                                    <li>
                                        <div class="post-thumb">
                                            <a href="{{ route('blogs.show', $post->slug) }}">
                                                <img src="{{ $post->thumbnail ? asset('storage/' . $post->thumbnail) : asset('frontend/assets/images/blog/blog-1.webp') }}"
                                                    alt="{{ $post->title }}"
                                                    style="height:60px; width:60px; object-fit:cover; border-radius:4px;">
                                            </a>
                                        </div>
                                        <div class="post-content">
                                            <h6 class="post-title">
                                                <a
                                                    href="{{ route('blogs.show', $post->slug) }}">{{ Str::limit($post->title, 40) }}</a>
                                            </h6>
                                            <div class="blog-meta">
                                                <ul>
                                                    <li>{{ $post->created_at->format('d M Y') }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="tj-sidebar-widget widget-categories wow fadeInUp" data-wow-delay=".5s">
                            <h4 class="widget-title">Categories</h4>
                            <ul>
                                @foreach ($categories as $category => $count)
                                    <li>
                                        <a href="{{ route('blog', ['category' => $category]) }}">
                                            {{ ucfirst($category) }}
                                            <span class="number">({{ $count }})</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        $(document).on('click', '#pagination-links a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#blog-list').html(res.blogs);
                    $('#pagination-links').html(res.pagination);
                    $('html, body').animate({
                        scrollTop: $("#blog-list").offset().top - 100
                    }, 500);
                }
            });
        });

        $(document).on('click', '.text-btn.disabled', function() {
            alert('No details available for this post yet.');
        });
    </script>
@endpush
