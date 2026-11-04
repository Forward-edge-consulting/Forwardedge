@extends('admin.master_page')
@php
    $isNew = !$event->exists;
    $when = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d\TH:i') : '';
@endphp
@section('title', ($isNew ? 'New event' : $event->title) . ' | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>{{ $isNew ? 'New event' : $event->title }}</h1>
            @unless ($isNew)<p class="fe-title-sub">{{ $event->category_label }}, {{ $event->is_past ? 'past' : 'upcoming' }}</p>@endunless
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.events.list') }}" class="btn btn-outline-secondary">Back to events</a>
            @if (!$isNew && $event->status === 'published')
                <a href="{{ route('events.show', $event->slug) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">View live <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" id="eventForm" data-unsaved
          action="{{ $isNew ? route('admin.events.store') : route('admin.events.update', $event) }}">
        @csrf
        @unless ($isNew) @method('PUT') @endunless
        <input type="hidden" name="highlights_json" id="highlightsJson" value="{{ json_encode($event->highlights ?? []) }}">

        <div class="fb-grid">
            <div class="fb-main">
                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">Basics</h2>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="ev-title">Event name</label>
                            <input id="ev-title" name="title" class="form-control" required maxlength="255" value="{{ old('title', $event->title) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ev-category">Category</label>
                            <select id="ev-category" name="category_id" class="form-select">
                                <option value="">No category</option>
                                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected((int) old('category_id', $event->category_id) === $c->id)>{{ $c->name }}</option>@endforeach
                            </select>
                            <div class="form-text"><a href="{{ route('admin.event-categories.index') }}">Manage categories</a></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ev-status">Status</label>
                            <select id="ev-status" name="status" class="form-select">
                                <option value="draft" @selected(old('status', $event->status) === 'draft')>Draft, hidden from the site</option>
                                <option value="published" @selected(old('status', $event->status) === 'published')>Published</option>
                                <option value="cancelled" @selected(old('status', $event->status) === 'cancelled')>Cancelled, hidden</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ev-summary">Summary</label>
                            <textarea id="ev-summary" name="short_description" class="form-control" rows="3" maxlength="500">{{ old('short_description', $event->short_description) }}</textarea>
                            <div class="form-text">One or two sentences. Shown on the events page and at the top of the event.</div>
                        </div>
                    </div>
                </section>

                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">When and where</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="ev-start">Starts</label>
                            <input id="ev-start" type="datetime-local" name="start_date" class="form-control" required value="{{ old('start_date', $when($event->start_date)) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ev-end">Ends (optional)</label>
                            <input id="ev-end" type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date', $when($event->end_date)) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ev-loc">City or place</label>
                            <input id="ev-loc" name="location" class="form-control" required maxlength="255" placeholder="Lagos, or Online" value="{{ old('location', $event->location) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ev-venue">Venue (optional)</label>
                            <input id="ev-venue" name="venue" class="form-control" maxlength="255" value="{{ old('venue', $event->venue) }}">
                        </div>
                    </div>
                </section>

                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">Cover image</h2>
                    @if ($event->thumbnail)<img src="{{ Storage::url($event->thumbnail) }}" alt="" class="ev-cover mb-3">@endif
                    <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">Used on the events page and as the picture on the event. JPG, PNG or WebP, up to 4 MB.</div>
                </section>

                <section class="fe-panel fb-section">
                    <div class="fe-panel-head"><h2 class="fe-h3">Highlights</h2></div>
                    <p class="fe-item-s mb-3" style="white-space:normal">The numbers you are proud of, such as attendees or speakers. Shown on the Past events wall and on the event.</p>
                    <div id="hlRows" class="ev-rows"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="hlAdd">Add a highlight</button>
                </section>

                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">Call to action</h2>
                    <p class="fe-item-s mb-3" style="white-space:normal">One button on the event while it is upcoming. It disappears once the event is over.</p>
                    @php $cta = old('cta_type', $event->cta_type ?? 'none'); @endphp
                    <div class="ev-cta">
                        @foreach (['none' => 'No button', 'interest' => 'Express interest form', 'link' => 'Link to another site, for example a ticketing page', 'contact' => 'Contact us'] as $v => $l)
                            <label class="be-radio"><input type="radio" name="cta_type" value="{{ $v }}" @checked($cta === $v)> {{ $l }}</label>
                        @endforeach
                    </div>
                    <div class="row g-3 mt-1" id="ctaFields">
                        <div class="col-md-6" data-cta="interest link contact">
                            <label class="form-label" for="ev-cta-label">Button text</label>
                            <input id="ev-cta-label" name="cta_label" class="form-control" maxlength="60" placeholder="Express interest" value="{{ old('cta_label', $event->cta_label) }}">
                        </div>
                        <div class="col-md-6" data-cta="interest">
                            <label class="form-label" for="ev-cta-form">Form</label>
                            <select id="ev-cta-form" name="cta_form_id" class="form-select">
                                <option value="">Express interest (default)</option>
                                @foreach ($forms as $f)<option value="{{ $f->id }}" @selected((int) old('cta_form_id', $event->cta_form_id) === $f->id)>{{ $f->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12" data-cta="link">
                            <label class="form-label" for="ev-cta-url">Web address</label>
                            <input id="ev-cta-url" name="cta_url" class="form-control" maxlength="500" placeholder="https://" value="{{ old('cta_url', $event->cta_url) }}">
                        </div>
                    </div>
                </section>

                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">Link</h2>
                    <label class="form-label" for="ev-slug">Link ending</label>
                    <div class="input-group">
                        <span class="input-group-text">/events/</span>
                        <input id="ev-slug" name="slug" class="form-control" maxlength="190" value="{{ old('slug', $event->slug) }}" placeholder="generated from the name">
                    </div>
                    <div class="form-text">When the event has a published page, this link sends visitors to that page.</div>
                </section>

                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif

                <div class="d-flex flex-wrap gap-2 align-items-center fb-save">
                    <button type="submit" class="btn btn-primary">{{ $isNew ? 'Save event' : 'Save changes' }}</button>
                </div>
            </div>

            <aside class="fb-preview" id="page" aria-label="Event page">
                <p class="fe-label-sm">Event page</p>
                <div class="fb-preview-card">
                    @if ($isNew)
                        <p class="fe-item-s mb-0" style="white-space:normal">Save the event first. Then you can build a page for it from a template.</p>
                    @elseif ($page)
                        <p class="mb-1"><strong>{{ $page->title }}</strong></p>
                        <p class="fe-item-s" style="white-space:normal">{{ $page->status === 'published' ? 'Live. This is the page visitors see.' : 'Draft. Publish it when it is ready.' }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-primary" href="{{ route('pb.blocks', $page) }}">Edit the page</a>
                            @if ($page->status === 'published')<a class="btn btn-outline-secondary" href="{{ route('page.show', $page->slug) }}" target="_blank" rel="noopener">View</a>@endif
                        </div>
                        @if ($event->is_past)
                            <hr>
                            <p class="fe-item-s" style="white-space:normal">The event is over. Add the numbers and the story.</p>
                            <button type="submit" class="btn btn-outline-secondary" form="recapForm">Add recap sections</button>
                        @endif
                    @else
                        <p class="fe-item-s" style="white-space:normal">No page yet. Start from a template and edit it.</p>
                        <button type="submit" form="pageForm" name="template" value="{{ $event->is_past ? 'event_recap' : 'event_upcoming' }}" class="btn btn-primary">
                            Build {{ $event->is_past ? 'the recap page' : 'the event page' }}
                        </button>
                    @endif
                </div>
                @unless ($isNew)
                    <button type="submit" form="deleteForm" class="btn btn-sm btn-outline-danger mt-3">Delete event</button>
                @endunless
            </aside>
        </div>
    </form>

    @unless ($isNew)
        {{-- Separate forms (HTML does not allow forms inside forms); the side-panel buttons point at them. --}}
        <form method="POST" id="pageForm" action="{{ route('admin.events.page.create', $event) }}">@csrf</form>
        <form method="POST" id="recapForm" action="{{ route('admin.events.page.recap', $event) }}">@csrf</form>
        <form method="POST" id="deleteForm" action="{{ route('admin.events.destroy', $event) }}"
              data-confirm="“{{ Str::limit($event->title, 80) }}” will be deleted.{{ $page ? ' Its page is kept as a draft.' : '' }}"
              data-confirm-title="Delete this event?" data-confirm-action="Delete event">
            @csrf @method('DELETE')
        </form>
    @endunless
</div>
@endsection

@push('scripts')
<script>
(function () {
    var rows = @json($event->highlights ?? []), box = document.getElementById('hlRows'), out = document.getElementById('highlightsJson');
    function sync() { out.value = JSON.stringify(rows); }
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
    function render() {
        box.innerHTML = rows.map(function (r, i) {
            return '<div class="ev-row" data-i="' + i + '">' +
                '<input class="form-control fe-num" data-k="value" value="' + esc(r.value) + '" placeholder="120" aria-label="Number" maxlength="24">' +
                '<input class="form-control" data-k="label" value="' + esc(r.label) + '" placeholder="Attendees" aria-label="Label" maxlength="90">' +
                '<button type="button" class="btn btn-sm btn-outline-danger" data-del aria-label="Remove highlight"><i class="bi bi-trash" aria-hidden="true"></i></button></div>';
        }).join('');
        document.getElementById('hlAdd').disabled = rows.length >= 6;
        sync();
    }
    box.addEventListener('input', function (e) { var r = e.target.closest('.ev-row'); if (!r) return; rows[+r.dataset.i][e.target.dataset.k] = e.target.value; sync(); });
    box.addEventListener('click', function (e) { var b = e.target.closest('[data-del]'); if (!b) return; rows.splice(+b.closest('.ev-row').dataset.i, 1); render(); });
    document.getElementById('hlAdd').addEventListener('click', function () { rows.push({ value: '', label: '' }); render(); var l = box.querySelectorAll('.ev-row'); l[l.length - 1].querySelector('input').focus(); });
    render();

    var radios = document.querySelectorAll('input[name=cta_type]'), groups = document.querySelectorAll('[data-cta]');
    function cta() {
        var v = (document.querySelector('input[name=cta_type]:checked') || {}).value;
        groups.forEach(function (g) { g.hidden = g.dataset.cta.split(' ').indexOf(v) === -1; });
    }
    radios.forEach(function (r) { r.addEventListener('change', cta); }); cta();
})();
</script>
@endpush
