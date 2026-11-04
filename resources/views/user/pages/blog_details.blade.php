@php use Illuminate\Support\Str; @endphp
@extends('user.master_page')
@php
    $seoTitle = $blog->meta_title ?: $blog->title ?: 'Read Blog';
    $excerpt = $blog->meta_description
        ?? Str::limit(strip_tags(optional($blog->details->first())->content ?? $blog->title), 160);
    $seoImage = $blog->thumbnail ? asset('storage/' . $blog->thumbnail) : null;
@endphp
@section('title', $seoTitle . ' | Forward Edge Consulting')
@section('meta')
    @if (!empty($isPreview))<meta name="robots" content="noindex, nofollow">@endif
    <meta name="description" content="{{ $excerpt }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $excerpt }}">
    @if($seoImage)
        <meta property="og:image" content="{{ $seoImage }}">
    @endif
@endsection
@push('jsonld')
@php
$_jsonLd = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => $blog->title,
    'description'   => $excerpt,
    'author'        => ['@type' => 'Person', 'name' => $blog->author->name ?? 'Forward Edge Consulting'],
    'publisher'     => ['@type' => 'Organization', 'name' => 'Forward Edge Consulting'],
    'datePublished' => $blog->created_at->toIso8601String(),
    'dateModified'  => $blog->updated_at->toIso8601String(),
];
if ($seoImage) $_jsonLd['image'] = $seoImage;
@endphp
<script type="application/ld+json">{!! json_encode($_jsonLd) !!}</script>
@endpush
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/forms.css') }}?v=4">
    <style>
        .blog-video-frame {
            position: relative;
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            background: #111;
        }

        .blog-video-frame.landscape::before,
        .blog-video-frame.portrait::before {
            content: "";
            display: block;
        }

        .blog-video-frame.landscape::before {
            padding-top: 56.25%;
        }

        .blog-video-frame.portrait::before {
            padding-top: 125%;
        }

        .blog-video-frame iframe,
        .blog-video-frame video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
            object-fit: cover;
            background: #000;
        }
    </style>
@endpush
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.reply-trigger').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const canReply = btn.dataset.canReply === '1';
                    const targetId = btn.dataset.replyId;
                    if (!canReply) {
                        alert('Only Forward Edge admins can reply.');
                        return;
                    }
                    const target = document.getElementById(`reply-${targetId}`);
                    if (target) {
                        target.classList.toggle('d-none');
                        const textarea = target.querySelector('textarea');
                        if (textarea) textarea.focus();
                    }
                });
            });
        });
    </script>
@endpush
@section('main')
    @include('user.partials.breadcrumb')
    <section class="tj-blog-section section-gap slidebar-stickiy-container">
        <div class="container">
            <div class="row row-gap-5">
                <div class="col-lg-8">
                    <div class="post-details-wrapper">
                        @if (!empty($isPreview))
                            <div class="fe-preview-banner" role="status">Preview. This post is {{ $blog->status === 'scheduled' ? 'scheduled for ' . $blog->published_at->format('j M Y, g:ia') : 'a draft' }} and is only visible to you.</div>
                        @endif
                        {{-- Blog Image --}}
                        <div class="blog-images wow fadeInUp" data-wow-delay=".1s">
                            <img src="{{ $blog->thumbnail
                                ? asset('storage/' . $blog->thumbnail)
                                : asset('frontend/assets/images/service/service-1.webp') }}"
                                alt="{{ $blog->title ?? 'Blog Post' }}" class="img-fluid"
                                style="height: 300px; width: 100%; object-fit: cover; border-radius: 8px;">
                        </div>

                        {{-- Blog Title --}}
                        <h2 class="title title-anim">
                            {{ $blog->title ?? 'Unlocking Business Potential: Innovative Solutions for Unmatched Success' }}
                        </h2>

                        {{-- Blog Meta --}}
                        <div class="blog-category-two wow fadeInUp" data-wow-delay=".3s">
                            <div class="category-item">
                                <div class="cate-images">
                                    <img src="{{ $blog->author && $blog->author->avatar
                                        ? asset($blog->author->avatar)
                                        : asset('backend/assets/img/avatar-2.jpg') }}"
                                        alt="Author" class="rounded-circle"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <div class="cate-text">
                                    <span class="degination">Authored by</span>
                                    <h6 class="title">{{ $blog->author->name ?? 'Forward Edge Team' }}</h6>
                                </div>
                            </div>
                            <div class="category-item">
                                <div class="cate-icons"><i class="tji-calendar"></i></div>
                                <div class="cate-text">
                                    <span class="degination">Date Released</span>
                                    <h6 class="text">
                                        {{ $blog->display_date ? $blog->display_date->format('d F, Y') : now()->format('d F, Y') }}
                                    </h6>
                                </div>
                            </div>
                            <div class="category-item">
                                <div class="cate-icons"><i class="tji-comment"></i></div>
                                <div class="cate-text">
                                    <span class="degination">Category</span>
                                    <h6 class="text">{{ $blog->category ?? 'Business Strategy' }}</h6>
                                </div>
                            </div>
                        </div>

                        {{-- Blog Body --}}
                        <div class="blog-text">
                            @php
                                $blocks = $blog->details->sortBy('order');
                                $linkify = function ($text) {
                                    $escaped = e($text);
                                    return preg_replace('~(https?://[^\s<]+)~i', '<a href="$1" target="_blank" rel="noopener" class="text-primary text-decoration-underline">$1</a>', $escaped);
                                };
                            @endphp
                            @if ($blocks->isNotEmpty())
                                @include('user.pages.partials.blog-blocks', ['blog' => $blog, 'blocks' => $blocks, 'linkify' => $linkify])
                            @else
                                {{-- Enhanced Dummy Content --}}
                                <p class="wow fadeInUp" data-wow-delay=".3s">
                                    In today's rapidly evolving business landscape, organizations face unprecedented
                                    challenges
                                    and opportunities. At Forward Edge Consulting, we understand that success isn't just
                                    about
                                    adapting to change—it's about staying ahead of the curve and leading your industry
                                    through
                                    strategic innovation and operational excellence.
                                </p>

                                <p class="wow fadeInUp" data-wow-delay=".4s">
                                    Our comprehensive approach to business transformation combines cutting-edge technology,
                                    proven methodologies, and deep industry expertise to deliver measurable results. Whether
                                    you're looking to optimize your operations, enhance customer experience, or drive
                                    digital
                                    transformation, we're here to guide you every step of the way.
                                </p>

                                <blockquote class="wow fadeInUp" data-wow-delay=".5s">
                                    <p>Success in business isn't about following the crowd—it's about creating your own path
                                        and inspiring others to follow. Innovation coupled with strategic execution is the
                                        catalyst that transforms vision into reality.</p>
                                    <cite>Forward Edge Consulting Team</cite>
                                </blockquote>

                                <h3 class="wow fadeInUp" data-wow-delay=".6s">Key Pillars of Business Excellence</h3>

                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="wow fadeInUp" data-wow-delay=".7s">
                                            <li><span><i class="tji-check"></i></span>Strategic Innovation Framework</li>
                                            <li><span><i class="tji-check"></i></span>Customer-Centric Design Thinking</li>
                                            <li><span><i class="tji-check"></i></span>Data-Driven Decision Making</li>
                                            <li><span><i class="tji-check"></i></span>Agile Leadership Development</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="wow fadeInUp" data-wow-delay=".8s">
                                            <li><span><i class="tji-check"></i></span>Digital Transformation Strategy</li>
                                            <li><span><i class="tji-check"></i></span>Operational Excellence Programs</li>
                                            <li><span><i class="tji-check"></i></span>Change Management Support</li>
                                            <li><span><i class="tji-check"></i></span>Sustainable Growth Practices</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="images-wrap">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="image-box wow fadeInUp" data-wow-delay=".9s">
                                                <img src="{{ asset('frontend/assets/images/service/service-4.webp') }}"
                                                    alt="Business Strategy" class="img-fluid">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="image-box wow fadeInUp" data-wow-delay="1.0s">
                                                <img src="{{ asset('frontend/assets/images/service/service-2.webp') }}"
                                                    alt="Team Collaboration" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h3 class="wow fadeInUp" data-wow-delay="1.1s">Driving Results Through Partnership</h3>
                                <p class="wow fadeInUp" data-wow-delay="1.2s">
                                    Our success is measured by your success. We don't just provide consulting services—we
                                    become your strategic partner, working alongside your team to achieve sustainable growth
                                    and competitive advantage. From initial assessment to implementation and beyond, we're
                                    committed to delivering excellence at every stage of our engagement.
                                </p>
                            @endif
                        </div>

                        {{-- Tags and Share --}}
                        <div class="tj-tags-post wow fadeInUp" data-wow-delay=".3s">
                            <div class="tagcloud">
                                <span>Tags:</span>
                                <a href="#">Business Strategy</a>
                                <a href="#">Innovation</a>
                                <a href="#">Leadership</a>
                                <a href="#">Growth</a>
                            </div>
                            <div class="post-share">
                                <ul>
                                    <li>Share:</li>
                                    <li><a href="https://facebook.com/forwardedgeconsulting/

" target="_blank"><i
                                                class="fa-brands fa-facebook-f"></i></a></li>
                                    <li><a href="https://x.com/ForwardEdgeNg" target="_blank"><i
                                                class="fa-brands fa-x-twitter"></i></a></li>
                                    <li><a href="https://www.instagram.com/forwardedge_consultingltd
" target="_blank"><i
                                                class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="https://www.linkedin.com/company/forward-edge-consulting-ltd/
" target="_blank"><i
                                                class="fa-brands fa-linkedin-in"></i></a></li>
                                </ul>
                            </div>
                        </div>

                        {{-- Navigation --}}
                        <div class="tj-post__navigation wow fadeInUp" data-wow-delay=".3s">
                            <div class="tj-nav__post previous">
                                <div class="tj-nav-post__nav prev_post">
                                    <a href="{{ route('blog') }}"><span><i class="tji-arrow-left"></i></span>Previous</a>
                                </div>
                            </div>
                            <div class="tj-nav-post__grid">
                                <a href="{{ route('blog') }}"><i class="tji-window"></i></a>
                            </div>
                            <div class="tj-nav__post next">
                                <div class="tj-nav-post__nav next_post">
                                    <a href="{{ route('blog') }}">Next<span><i class="tji-arrow-right"></i></span></a>
                                </div>
                            </div>
                        </div>

                        {{-- Comments Section --}}
                        <div class="tj-comments-container">
                            <div class="tj-comments-wrap mb-4">
                                <div class="comments-title">
                                    <h3 class="title">Comments ({{ $comments->total() }})</h3>
                                </div>
                                <div class="tj-latest-comments">
                                    <ul class="comments-list">
                                        @forelse($comments as $comment)
                                            <li class="tj-comment">
                                                <div class="comment-content">
                                                    <div class="comment-avatar">
                                                        <img src="{{ asset('frontend/assets/images/blog/avatar-1.jpg') }}"
                                                             alt="Author" class="rounded-circle"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    </div>
                                                    <div class="comments-header">
                                                        <div class="avatar-name d-flex align-items-center gap-2">
                                                            <h6 class="title mb-0">{{ $comment->name ?? $comment->user?->name ?? 'Guest' }}</h6>
                                                            @if($comment->is_admin_reply)
                                                                <span class="badge bg-primary">Admin</span>
                                                            @endif
                                                        </div>
                                                        <div class="comment-text d-flex align-items-center gap-2">
                                                            <span class="date">{{ optional($comment->created_at)->format('M j, Y \\a\\t H:i') }}</span>
                                                            <a class="reply btn btn-link p-0 reply-trigger"
                                                               data-reply-id="{{ $comment->id }}"
                                                               data-can-reply="{{ auth()->check() && auth()->user()->role === 'admin' ? '1' : '0' }}">
                                                                Reply
                                                            </a>
                                                        </div>
                                                        <div class="desc">
                                                            <p>{!! nl2br(e($comment->body)) !!}</p>
                                                        </div>
                                                        @if($comment->replies->isNotEmpty())
                                                            <ul class="comments-list ms-4 mt-3">
                                                                @foreach($comment->replies as $reply)
                                                                    <li class="tj-comment">
                                                                        <div class="comment-content">
                                                                            <div class="comment-avatar">
                                                                                <img src="{{ asset('frontend/assets/images/blog/avatar-2.jpg') }}"
                                                                                     alt="Author" class="rounded-circle"
                                                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                                            </div>
                                                                            <div class="comments-header">
                                                                                <div class="avatar-name d-flex align-items-center gap-2">
                                                                                    <h6 class="title mb-0">{{ $reply->name ?? 'Admin' }}</h6>
                                                                                    @if($reply->is_admin_reply)
                                                                                        <span class="badge bg-primary">Admin</span>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="comment-text">
                                                                                    <span class="date">{{ optional($reply->created_at)->format('M j, Y \\a\\t H:i') }}</span>
                                                                                </div>
                                                                                <div class="desc">
                                                                                    <p>{!! nl2br(e($reply->body)) !!}</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                        <div id="reply-{{ $comment->id }}" class="mt-3 d-none">
                                                            <form method="POST" action="{{ route('blog.comment.reply', [$blog->slug, $comment->id]) }}">
                                                                @csrf
                                                                <div class="mb-2">
                                                                    <textarea name="body" class="form-control" rows="4" style="min-height:120px" placeholder="Write your reply" required></textarea>
                                                                </div>
                                                                <button type="submit" class="btn btn-sm btn-primary">Send reply</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="text-muted">No comments yet.</li>
                                        @endforelse
                                    </ul>
                                    <div class="mt-3">
                                        {{ $comments->links() }}
                                    </div>
                                </div>
                            </div>

                            {{-- Comment Form --}}
                            <div class="tj-comments__container">
                                <div class="comment-respond">
                                    <h3 class="comment-reply-title">Leave a Comment</h3>
                                    <form class="comment-form" data-fe-loader method="post" action="{{ route('blog.comment.store', $blog->slug) }}">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-lg-6">
                                                <div class="tj-input-field">
                                                    <label for="cmt_name mb-1">Your Name</label>
                                                    <input type="text" id="cmt_name" name="name" placeholder="Name"
                                                           value="{{ auth()->user()->name ?? '' }}"
                                                           @auth readonly @endauth required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="tj-input-field">
                                                    <label for="cmt_email mb-1">Email</label>
                                                    <input type="email" id="cmt_email" name="email" placeholder="Email"
                                                           value="{{ auth()->user()->email ?? '' }}"
                                                           @auth readonly @endauth required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="tj-input-field">
                                                    <label for="cmt_message mb-1">Write Comments</label>
                                                    <textarea id="cmt_message" name="body" placeholder="Your Message" rows="5" required></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <x-turnstile appearance="interaction-only" />
                                                <button type="submit" class="tj-primary-btn">
                                                    <span class="btn-text"><span>Post a comment</span></span>
                                                    <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">
                    <div class="tj-main-sidebar slidebar-stickiy">
                        {{-- Related Posts Widget --}}
                        <div class="tj-sidebar-widget tj-recent-posts wow fadeInUp" data-wow-delay=".3s">
                            <h4 class="widget-title">Related Articles</h4>
                            <ul>
                                @forelse($relatedBlogs as $related)
                                    <li>
                                        <div class="post-thumb">
                                            <a href="{{ route('blogs.show', $related->slug) }}">
                                                <img src="{{ $related->thumbnail
                                                    ? asset('storage/' . $related->thumbnail)
                                                    : asset('frontend/assets/images/blog/blog-1.webp') }}"
                                                    alt="{{ $related->title }}" class="img-fluid"
                                                    style="width: 80px; height: 60px; object-fit: cover;">
                                            </a>
                                        </div>
                                        <div class="post-content">
                                            <h6 class="post-title">
                                                <a href="{{ route('blogs.show', $related->slug) }}">
                                                    {{ Str::limit($related->title, 50) }}
                                                </a>
                                            </h6>
                                            <div class="blog-meta">
                                                <ul>
                                                    <li>{{ $related->created_at->format('d M Y') }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li>No related articles yet.</li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- Categories Widget --}}
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



                        {{-- Newsletter Widget --}}
                        <div class="tj-sidebar-widget widget-feature-item wow fadeInUp" data-wow-delay=".9s">
                            <div class="feature-box">
                                <div class="feature-content">
                                    <h2 class="title">Stay Updated</h2>
                                    <span>Get Latest Insights</span>
                                    <form class="newsletter-form js-newsletter-form" action="{{ route('newsletter.subscribe', [], false) }}" method="POST"
                                        data-block-id="blog-newsletter"
                                        data-tags='["BlogSidebar"]'>
                                        @csrf
                                        <input type="hidden" name="form_tags" value="BlogSidebar">
                                        <div class="form-input mb-3">
                                            <input type="email" name="email" placeholder="Your Email" required
                                                data-field-input
                                                data-field-label="Email"
                                                data-field-name="email"
                                                data-field-type="email"
                                                data-field-required="1">
                                        </div>
                                        <label class="d-flex align-items-start gap-2 mb-3" style="font-size:13px;line-height:1.4;">
                                            <input type="checkbox" name="consent" value="1" required data-consent="I agree to receive emails from Forward Edge and accept the Privacy Policy." style="margin-top:3px;flex:none;">
                                            <span>I agree to receive emails from Forward Edge and accept the <a href="{{ route('privacy') }}">Privacy Policy</a>.</span>
                                        </label>
                                        <x-turnstile appearance="interaction-only" />
                                        <button type="submit" class="tj-primary-btn btn-sm">
                                            <span class="btn-text">Subscribe</span>
                                        </button>
                                        <div class="dynamic-form-feedback" role="status" aria-live="polite"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
