@extends('admin.master_page')
@section('title', ($blog->title ?: 'Post') . ' | Forward Edge')

@section('main')
<div class="be" id="blogEditor">
    <div class="be-bar">
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary">All posts</a>
        <div class="be-bar-status">
            <span class="badge" id="beBadge">Draft</span>
            <span class="be-saved" id="beSaved" role="status" aria-live="polite"></span>
        </div>
    </div>

    <div class="be-grid">
        <div class="be-main">
            <label class="visually-hidden" for="beTitle">Post title</label>
            <textarea id="beTitle" class="be-title" rows="1" placeholder="Post title" maxlength="180" autocomplete="off">{{ $blog->title === 'Untitled post' ? '' : $blog->title }}</textarea>
            <div class="be-slug">
                <span>forwardedgeconsulting.com/blog/</span><input id="beSlug" value="{{ $blog->slug }}" aria-label="Post link ending" maxlength="190" spellcheck="false">
            </div>

            <div id="beBlocks" class="be-blocks"></div>
            <div class="be-end">
                <button type="button" class="btn btn-outline-secondary" id="beAddEnd" aria-haspopup="true">Add a block</button>
                <span class="be-hint">Press Enter for a new paragraph. Select text to bold or link it.</span>
            </div>
        </div>

        <aside class="be-side" aria-label="Post settings">
            <section class="fe-panel be-publish">
                <p class="fe-label-sm">Publish</p>
                <div id="bePublish"></div>
            </section>

            <section class="fe-panel be-section">
                <h2 class="fe-h3">Post</h2>
                <div class="mb-3">
                    <label class="form-label" for="beCategory">Category</label>
                    <input id="beCategory" class="form-control" list="beCategories" value="{{ $blog->category }}" maxlength="80">
                    <datalist id="beCategories">@foreach ($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="beExcerpt">Short summary</label>
                    <textarea id="beExcerpt" class="form-control" rows="3" maxlength="300">{{ $blog->excerpt }}</textarea>
                    <div class="form-text">Shown in listings and search results.</div>
                </div>
                <div>
                    <span class="form-label d-block">Cover image</span>
                    <div class="be-cover" id="beCover">
                        @if ($blog->thumbnail)<img src="{{ Storage::url($blog->thumbnail) }}" alt="Cover image">@endif
                    </div>
                    <label class="btn btn-sm btn-outline-secondary mt-2 mb-0">
                        <span id="beCoverLabel">{{ $blog->thumbnail ? 'Replace image' : 'Upload image' }}</span>
                        <input type="file" id="beCoverFile" accept="image/jpeg,image/png,image/webp" hidden>
                    </label>
                </div>
            </section>

            <section class="fe-panel be-section">
                <h2 class="fe-h3">Newsletter signup</h2>
                <p class="fe-item-s mb-3" style="white-space:normal">Readers who sign up are saved in Responses, tagged with this post.</p>
                <div class="mb-3">
                    <label class="form-label" for="beSignupForm">Form</label>
                    <select id="beSignupForm" class="form-select">
                        @foreach ($forms as $f)
                            <option value="{{ $f->id }}" @selected($blog->signup_form_id == $f->id)>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="mb-3">
                    <legend class="form-label">Show it</legend>
                    <label class="be-radio"><input type="radio" name="beSignupPos" value="end" @checked($blog->signup_position === 'end')> At the end of the post</label>
                    <label class="be-radio"><input type="radio" name="beSignupPos" value="after_paragraph" @checked($blog->signup_position === 'after_paragraph')> After paragraph
                        <input type="number" id="beSignupAfter" class="form-control form-control-sm d-inline-block" style="width:72px" min="1" max="50" value="{{ $blog->signup_after ?? 3 }}" aria-label="Paragraph number"></label>
                    <label class="be-radio"><input type="radio" name="beSignupPos" value="none" @checked($blog->signup_position === 'none')> Not at all</label>
                </fieldset>
                <div>
                    <label class="form-label" for="beSignupHeading">Heading above the form</label>
                    <input id="beSignupHeading" class="form-control" value="{{ $blog->signup_heading }}" maxlength="160" placeholder="Get new posts by email">
                    <div class="form-text">If you add a signup block inside the post, it is used instead.</div>
                </div>
            </section>

            <section class="fe-panel be-section">
                <h2 class="fe-h3">Search appearance</h2>
                <div class="mb-3">
                    <label class="form-label" for="beMetaTitle">Title</label>
                    <input id="beMetaTitle" class="form-control" value="{{ $blog->meta_title }}" maxlength="255" placeholder="Defaults to the post title">
                </div>
                <div>
                    <label class="form-label" for="beMetaDesc">Description</label>
                    <textarea id="beMetaDesc" class="form-control" rows="3" maxlength="300">{{ $blog->meta_description }}</textarea>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.FE_BLOG = {
        id: {{ $blog->id }},
        status: @json($blog->status),
        publishedAt: @json($blog->published_at?->format('Y-m-d\TH:i')),
        publicUrl: @json(route('blogs.show', $blog->slug)),
        urls: {
            save: @json(route('admin.blogs.save', $blog->id)),
            upload: @json(route('admin.blogs.upload', $blog->id)),
            cover: @json(route('admin.blogs.cover', $blog->id)),
            publicBase: @json(url('/blog')),
        },
        forms: @json($forms->map(fn ($f) => ['id' => $f->id, 'name' => $f->name])),
        blocks: @json($blocks),
        titleIsPlaceholder: @json($blog->title === 'Untitled post'),
    };
</script>
<script src="{{ asset('backend/assets/js/fe-blog-editor.js') }}?v=1"></script>
@endpush
