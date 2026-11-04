@extends('admin.master_page')
@section('title', $page->exists ? 'Edit Page' : 'Create Page')

@section('main')
    <div class="container py-5">
        <div class="pagetitle">
            <h1>{{ $page->exists ? 'Page settings' : 'Create page' }}</h1>
            <a href="{{ route('pb.pages') }}" class="btn btn-outline-secondary">Back to pages</a>
        </div>

        <div class="image-box p-4">
            <form data-unsaved enctype="multipart/form-data" method="post" action="{{ $page->exists ? route('pb.pages.update', $page) : route('pb.pages.store') }}">
                @csrf @if ($page->exists)
                    @method('PUT')
                @endif

                @if (!$page->exists && !empty($templates))
                    <div class="mb-4">
                        <span class="form-label d-block">Start from</span>
                        <div class="pb-templates" role="radiogroup" aria-label="Page template">
                            @foreach ($templates as $key => $tpl)
                                <label class="pb-template">
                                    <input type="radio" name="template" value="{{ $key }}" @checked(old('template', 'blank') === $key)>
                                    <strong>{{ $tpl['name'] }}</strong>
                                    <span>{{ $tpl['description'] }}</span>
                                    <small>{{ $tpl['for'] }}</small>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input class="form-control" name="title" value="{{ old('title', $page->title) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input class="form-control" name="slug" value="{{ old('slug', $page->slug) }}">
                        <div class="form-text">Leave blank to auto-generate</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="draft" @selected(old('status', $page->status) == 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $page->status) == 'published')>Published</option>
                        </select>
                    </div>

                    {{-- ===== Owner binding ===== --}}
                    @php
                        $ownerType = old('owner_type', $page->pageable_type === \App\Models\Event::class ? 'event' : null);
                        $ownerId = old('owner_id', $page->pageable_id);
                    @endphp
                    <div class="col-12">
                        <label class="form-label">Bind To</label>
                        <div class="d-flex gap-3 align-items-center flex-wrap">
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="owner_type" value=""
                                    {{ !$ownerType ? 'checked' : '' }}>
                                <span class="form-check-label">Standalone Page</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="owner_type" value="event"
                                    {{ $ownerType === 'event' ? 'checked' : '' }}>
                                <span class="form-check-label">Event</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 owner-select owner-event"
                        style="display: {{ $ownerType === 'event' ? 'block' : 'none' }};">
                        <label class="form-label">Select Event</label>
                        <select class="form-select" name="owner_id" {{ $ownerType === 'event' ? 'required' : '' }}>
                            <option value="">Choose event…</option>
                            @foreach ($events ?? [] as $e)
                                <option value="{{ $e->id }}" @selected($ownerType === 'event' && $ownerId == $e->id)>{{ $e->title }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">A page will be attached to this event.</div>
                    </div>


                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save page</button>
                    @if ($page->exists)
                        <a href="{{ route('pb.blocks', $page) }}" class="btn btn-outline-secondary">Edit blocks</a>
                        <a href="{{ route('page.show', $page->slug) }}" class="btn btn-outline-secondary"
                            target="_blank" rel="noopener">Preview</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tiny toggle script --}}
    <script>
        document.addEventListener('change', function(e) {
            if (e.target.name !== 'owner_type') return;
            const val = e.target.value;
            document.querySelectorAll('.owner-select').forEach(el => el.style.display = 'none');
            const ownerId = document.querySelector('select[name="owner_id"]');
            if (ownerId) ownerId.required = false;
            if (val === 'event') {
                document.querySelector('.owner-event')?.style.setProperty('display', 'block');
                if (ownerId) ownerId.required = true;
            }
        });
    </script>
@endsection
