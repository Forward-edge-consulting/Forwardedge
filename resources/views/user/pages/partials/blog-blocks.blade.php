@php
    $signupForm = $blog->signup_form_id ? \App\Models\Form::find($blog->signup_form_id) : null;
    $hasFormBlock = $blocks->contains('type', 'form');
    $signupActive = $signupForm && $signupForm->isOpen() && !$hasFormBlock && $blog->signup_position !== 'none';
    $placeAfter = ($signupActive && $blog->signup_position === 'after_paragraph') ? max(1, (int) $blog->signup_after) : null;
    $signupShown = false;
    $paragraphCount = 0;
@endphp
                                @foreach ($blocks as $block)
                                    @switch($block->type)
                                        @case('heading')
                                            <h3 class="wow fadeInUp" data-wow-delay=".3s">{!! $linkify($block->contentString()) !!}</h3>
                                            @break
                                        @case('paragraph')
                                            @if ($block->isHtml())
                                                <p class="wow fadeInUp" data-wow-delay=".3s">{!! \App\Support\RichText::clean($block->contentString()) !!}</p>
                                            @else
                                                <p class="wow fadeInUp" data-wow-delay=".3s">{!! nl2br($linkify($block->contentString())) !!}</p>
                                            @endif
                                            @php $paragraphCount++; @endphp
                                            @if ($placeAfter && !$signupShown && $paragraphCount === $placeAfter)
                                                @include('user.pages.partials.blog-signup', ['form' => $signupForm, 'heading' => $blog->signup_heading])
                                                @php $signupShown = true; @endphp
                                            @endif
                                            @break
                                        @case('button')
                                            @php $btnUrl = $block->extras['url'] ?? null; @endphp
                                            @if ($btnUrl)
                                                <p class="wow fadeInUp" data-wow-delay=".3s">
                                                    <a class="tj-primary-btn" href="{{ $btnUrl }}" @if (preg_match('#^https?://#i', $btnUrl)) target="_blank" rel="noopener" @endif>
                                                        <span class="btn-text"><span>{{ $block->contentString() }}</span></span>
                                                        <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                                    </a>
                                                </p>
                                            @endif
                                            @break
                                        @case('form')
                                            @php $blockForm = \App\Models\Form::find($block->extras['form_id'] ?? 0); @endphp
                                            @if ($blockForm)
                                                @include('user.pages.partials.blog-signup', ['form' => $blockForm, 'heading' => $block->extras['heading'] ?? null])
                                            @endif
                                            @break
                                        @case('quote')
                                            <blockquote class="wow fadeInUp" data-wow-delay=".3s">
                                                <p>{!! $linkify($block->contentString()) !!}</p>
                                                <cite>{{ $block->quoteAuthor() }}</cite>
                                            </blockquote>
                                            @break
                                        @case('list')
                                            @php $items = $block->contentArray(); @endphp
                                            @if ($items)
                                                <ul class="wow fadeInUp" data-wow-delay=".3s">
                                                    @foreach ($items as $item)
                                                        <li><span><i class="tji-check"></i></span>{!! $linkify($item) !!}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            @break
                                        @case('image')
                                            @php $imageUrl = $block->imageUrl(); @endphp
                                            @if ($imageUrl)
                                                <figure class="image-box wow fadeInUp" data-wow-delay=".3s">
                                                    <img src="{{ $imageUrl }}" alt="Blog Image" class="img-fluid">
                                                    @if (!empty($block->extras['caption']))
                                                        <figcaption class="text-muted small mt-2">{{ $block->extras['caption'] }}</figcaption>
                                                    @endif
                                                </figure>
                                            @endif
                                            @break
                                        @case('video')
                                            @php
                                                $videoUrl = $block->videoUrl();
                                                $orientation = $block->videoOrientation();
                                                $isExternal = $block->videoIsExternal();
                                                $isYouTube = $isExternal && \Illuminate\Support\Str::contains(strtolower($videoUrl), ['youtube.com', 'youtu.be']);
                                                $isVimeo = $isExternal && \Illuminate\Support\Str::contains(strtolower($videoUrl), ['vimeo.com']);

                                                $embedUrl = null;
                                                if ($isYouTube) {
                                                    if (preg_match('/v=([^&]+)/', $videoUrl, $matches)) {
                                                        $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                                    } elseif (preg_match('#youtu\\.be/([^?]+)#', $videoUrl, $matches)) {
                                                        $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                                    }
                                                } elseif ($isVimeo && preg_match('#vimeo\\.com/(\\d+)#', $videoUrl, $matches)) {
                                                    $embedUrl = 'https://player.vimeo.com/video/' . $matches[1];
                                                }
                                            @endphp
                                            @if ($videoUrl)
                                                <div class="blog-video-frame {{ $orientation === 'portrait' ? 'portrait' : 'landscape' }} wow fadeInUp"
                                                    data-wow-delay=".3s">
                                                    @if ($embedUrl)
                                                        <iframe src="{{ $embedUrl }}" frameborder="0"
                                                            allow="autoplay; fullscreen; picture-in-picture"
                                                            allowfullscreen></iframe>
                                                    @elseif ($isExternal)
                                                        <a class="btn btn-outline-primary w-100 h-100 d-flex align-items-center justify-content-center text-center"
                                                            href="{{ $videoUrl }}" target="_blank" rel="noopener">
                                                            Watch video
                                                        </a>
                                                    @else
                                                        <video controls preload="metadata" playsinline>
                                                            <source src="{{ $videoUrl }}">
                                                            Your browser does not support HTML5 video.
                                                        </video>
                                                    @endif
                                                </div>
                                            @endif
                                            @break
                                        @default
                                            <p class="wow fadeInUp" data-wow-delay=".3s">{{ $block->contentString() }}</p>
                                            @break
                                    @endswitch
                                @endforeach
                                @if ($signupActive && !$signupShown)
                                    @include('user.pages.partials.blog-signup', ['form' => $signupForm, 'heading' => $blog->signup_heading])
                                @endif
