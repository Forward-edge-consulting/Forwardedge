@extends('admin.master_page')
@section('title', 'Page Builder • ' . $page->title)

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <style>
    .select2-container .select2-selection--single {
        height: 38px;
        border-radius: 12px;
        border: 1px solid #ced4da;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 6px;
    }
  </style>
@endpush

@section('main')
@php
$blockMeta = [
    'page_header'      => ['icon' => 'bi-layout-text-sidebar', 'color' => '#5f7174', 'label' => 'Page header'],
    'rich_text'        => ['icon' => 'bi-text-paragraph',      'color' => '#5f7174', 'label' => 'Text'],
    'stats'            => ['icon' => 'bi-123',                 'color' => '#5f7174', 'label' => 'Numbers'],
    'form'             => ['icon' => 'bi-ui-checks-grid',      'color' => '#5f7174', 'label' => 'Form'],
    'events_grid'      => ['icon' => 'bi-calendar-event',      'color' => '#5f7174', 'label' => 'Events list'],
    'event_facts'      => ['icon' => 'bi-geo-alt',             'color' => '#5f7174', 'label' => 'Event details'],
    'cta_band'         => ['icon' => 'bi-megaphone',           'color' => '#5f7174', 'label' => 'Call to action band'],
    'faq_list'         => ['icon' => 'bi-patch-question',      'color' => '#5f7174', 'label' => 'Questions (FAQ)'],
    'feature_grid'     => ['icon' => 'bi-grid-3x2-gap',        'color' => '#5f7174', 'label' => 'Feature cards'],
    'steps'            => ['icon' => 'bi-list-ol',             'color' => '#5f7174', 'label' => 'Steps'],
    'quote_list'       => ['icon' => 'bi-chat-quote',          'color' => '#5f7174', 'label' => 'Testimonials'],
    'contact_info'     => ['icon' => 'bi-telephone',           'color' => '#5f7174', 'label' => 'Contact details'],
    'hero'             => ['icon' => 'bi-star-fill',                    'color' => '#5f7174'],
    'hero2'            => ['icon' => 'bi-layout-text-window-reverse',   'color' => '#5f7174'],
    'hero3'            => ['icon' => 'bi-badge-hd',                     'color' => '#5f7174'],
    'hero4'            => ['icon' => 'bi-rocket-takeoff',               'color' => '#5f7174'],
    'program_overview' => ['icon' => 'bi-journal-bookmark',             'color' => '#5f7174'],
    'overview'         => ['icon' => 'bi-grid-3x3-gap',                 'color' => '#5f7174'],
    'overview2'        => ['icon' => 'bi-collection',                   'color' => '#5f7174'],
    'about'            => ['icon' => 'bi-people',                       'color' => '#5f7174'],
    'about2'           => ['icon' => 'bi-person-lines-fill',            'color' => '#5f7174'],
    'sections'         => ['icon' => 'bi-layout-three-columns',         'color' => '#5f7174'],
    'sections2'        => ['icon' => 'bi-layout-wtf',                   'color' => '#5f7174'],
    'marquees'         => ['icon' => 'bi-arrows-move',                  'color' => '#5f7174'],
    'logo_slider'      => ['icon' => 'bi-images',                       'color' => '#5f7174'],
    'gallary'          => ['icon' => 'bi-camera',                       'color' => '#5f7174'],
    'testimonial'      => ['icon' => 'bi-chat-quote',                   'color' => '#5f7174'],
    'faq'              => ['icon' => 'bi-question-circle',              'color' => '#5f7174'],
    'closing_cta'      => ['icon' => 'bi-megaphone',                    'color' => '#5f7174'],
    'form_dark'        => ['icon' => 'bi-ui-checks',                    'color' => '#5f7174'],
    'form_light'       => ['icon' => 'bi-ui-checks-grid',              'color' => '#5f7174'],
];
@endphp
    <div class="container py-5 pb-wide">

        {{-- Header --}}
        <div class="pagetitle">
            <div>
                <h1>{{ $page->title }}</h1>
                <p class="fe-title-sub">Drag or use the arrows to reorder. Edits save as you go.</p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('pb.pages.edit', $page) }}">Page settings</a>
                <a class="btn btn-outline-secondary" href="{{ route('page.show', $page->slug) }}" target="_blank" rel="noopener">
                    View live <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        {{-- Alerts (non-AJAX fallback) --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Please fix the following:</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="pb-split">
        <div class="pb-left">
        {{-- Add Block toolbar --}}
        <div class="card rounded-12 shadow-soft mb-4 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="text-muted small">
                    Sections on this page: <strong>{{ $blocks->count() }}</strong>
                </div>
                <button class="btn btn-primary" id="openBlockPickerBtn">
                    <i class="bi bi-plus-circle me-1"></i> Add Block
                </button>
            </div>
        </div>

        {{-- Blocks List --}}
        <div class="card rounded-12 shadow-soft">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Blocks</div>
                <small class="text-muted">Order saves automatically</small>
            </div>
            <div class="card-body p-0">
                <div id="blockLive" class="visually-hidden" role="status" aria-live="polite"></div>
                <ul class="list-group list-group-flush" id="blockList">
                    @forelse($blocks as $b)
                        @php
                            $meta = $blockMeta[$b->type] ?? ['icon' => 'bi-puzzle', 'color' => '#5f7174'];
                            $preview = \Illuminate\Support\Str::limit(
                                $b->data['title'] ?? $b->data['subtitle'] ?? $b->data['text']
                                    ?? ($b->data['items'][0]['title'] ?? null)
                                    ?? ($b->data['plans'][0]['title'] ?? null)
                                    ?? ($b->data['kicker'] ?? null)
                                    ?? '', 60
                            );
                        @endphp
                        <li class="list-group-item px-3 py-3 block-row" draggable="true" data-id="{{ $b->id }}">
                            <div class="d-flex align-items-center gap-3">
                                <span class="drag-handle text-muted" style="cursor:grab;user-select:none" title="Drag to reorder" aria-hidden="true"><i class="bi bi-grip-vertical"></i></span>

                                {{-- Coloured type icon --}}
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:40px;height:40px;background:{{ $meta['color'] }}22;color:{{ $meta['color'] }}">
                                    <i class="bi {{ $meta['icon'] }} fs-5"></i>
                                </div>

                                {{-- Info --}}
                                <div class="flex-grow-1" style="min-width:0">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-semibold">{{ $meta['label'] ?? ucfirst(str_replace('_', ' ', $b->type)) }}</span>
                                        @if($b->variant && $b->variant !== 'default')
                                            <span class="badge bg-secondary-subtle text-secondary fw-normal">{{ $b->variant }}</span>
                                        @endif
                                        <span class="badge {{ $b->is_published ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}"
                                              data-pub-badge>{{ $b->is_published ? 'Live' : 'Draft' }}</span>
                                    </div>
                                    @if($preview)
                                        <div class="small text-muted text-truncate" data-block-preview>{{ $preview }}</div>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="d-flex gap-1 flex-shrink-0">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-action="move-up"
                                            aria-label="Move block up" title="Move up"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-action="move-down"
                                            aria-label="Move block down" title="Move down"><i class="bi bi-arrow-down" aria-hidden="true"></i></button>
                                    <button class="btn btn-outline-secondary btn-sm"
                                            data-action="toggle-publish"
                                            data-id="{{ $b->id }}"
                                            data-published="{{ $b->is_published ? '1' : '0' }}"
                                            title="{{ $b->is_published ? 'Unpublish' : 'Publish' }}">
                                        <i class="bi {{ $b->is_published ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" data-action="edit"
                                            data-payload="{{ base64_encode(json_encode($b)) }}" title="Edit block">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" data-action="clone"
                                            data-id="{{ $b->id }}" title="Duplicate block">
                                        <i class="bi bi-files"></i>
                                    </button>
                                    <form method="POST" action="{{ route('pb.blocks.destroy', $b) }}" data-action="delete">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit" title="Delete block">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-5">
                            No blocks yet. Click <strong>Add Block</strong> above to get started.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        </div>{{-- /pb-left --}}

        {{-- Live preview: click a section to edit it --}}
        <div class="pb-right">
            <div class="pb-preview" id="pbPreview">
                <div class="pb-preview-bar">
                    <span class="fe-label-sm mb-0">Live preview</span>
                    <div class="pb-devices" role="group" aria-label="Preview size">
                        <button type="button" class="btn btn-sm btn-outline-secondary is-active" data-device="100%" aria-pressed="true">Desktop</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-device="820px" aria-pressed="false">Tablet</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-device="390px" aria-pressed="false">Phone</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="pbReload" aria-label="Refresh preview" title="Refresh"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="pb-preview-stage">
                    <iframe id="previewFrame" title="Page preview"
                            src="{{ route('pb.blocks.preview', $page) }}?pb_edit=1"
                            sandbox="allow-same-origin allow-scripts allow-forms"></iframe>
                </div>
                <p class="be-hint">Click any section in the preview to edit it. Drafts are included.</p>
            </div>
        </div>
        </div>{{-- /pb-split --}}
    </div>

    {{-- ============== Block Picker Modal ============== --}}
    <div class="modal fade" id="blockPickerModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-grid me-2"></i>Choose a Block Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="search" class="form-control mb-3" id="pickerSearch" placeholder="Search block types…">
                    <div class="row g-3" id="pickerGrid"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============== Offcanvas Panel (Add/Edit) ============== --}}
    <div class="offcanvas offcanvas-end" id="blockPanel" tabindex="-1" aria-labelledby="blockPanelLabel"
         style="width:min(600px,100vw)">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="blockPanelLabel">Configure Block</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column" style="height:calc(100% - 57px)">
            <form id="modalForm" method="POST" enctype="multipart/form-data" novalidate
                  class="d-flex flex-column h-100">
                @csrf
                {{-- Type / Variant / Published row --}}
                <div class="p-3 border-bottom flex-shrink-0">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small-label">Block Type</label>
                            <select class="form-select" id="modal_type" name="type" required>
                                @foreach ($blockTypes as $t)
                                    <option value="{{ $t }}">{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small-label">Variant</label>
                            <select class="form-select" id="modal_variant" name="variant">
                                <option value="">Default</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small-label">Published</label>
                            <select class="form-select" name="is_published" id="modal_published">
                                <option value="1" selected>Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Scrollable dynamic fields --}}
                <div id="fieldMount" class="flex-grow-1 p-3" style="overflow-y:auto"></div>

                {{-- Sticky footer --}}
                <div class="border-top p-3 d-flex gap-2 justify-content-end bg-white flex-shrink-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmit">Save</button>
                </div>
            </form>
        </div>
    </div>


    {{-- ===================== JS ===================== --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        /* ====== CONFIG ====== */
        const VARIANTS = @json($variants);
        const PICKER_TYPES = @json($pickerTypes ?? []);
        const FORM_OPTIONS = @json($formOptions ?? []);
        const EVENT_TYPES = @json($eventTypes ?? []);
        const STORAGE_ROOT = @json(rtrim(asset('storage'), '/'));

        /* ====== BLOCK TYPE METADATA (icons + colors for cards & picker) ====== */
        const NEW_TYPES = ['page_header', 'rich_text', 'stats', 'form', 'events_grid', 'event_facts', 'cta_band', 'faq_list', 'feature_grid', 'steps', 'quote_list', 'contact_info'];
        const BLOCK_TYPE_META = {
            page_header:      { icon: 'bi-layout-text-sidebar',         color: '#5f7174', label: 'Page header' },
            rich_text:        { icon: 'bi-text-paragraph',              color: '#5f7174', label: 'Text' },
            stats:            { icon: 'bi-123',                         color: '#5f7174', label: 'Numbers' },
            form:             { icon: 'bi-ui-checks-grid',              color: '#5f7174', label: 'Form' },
            events_grid:      { icon: 'bi-calendar-event',              color: '#5f7174', label: 'Events list' },
            event_facts:      { icon: 'bi-geo-alt',                     color: '#5f7174', label: 'Event details' },
            cta_band:         { icon: 'bi-megaphone',                   color: '#5f7174', label: 'Call to action band' },
            faq_list:         { icon: 'bi-patch-question',              color: '#5f7174', label: 'Questions (FAQ)' },
            feature_grid:     { icon: 'bi-grid-3x2-gap',                color: '#5f7174', label: 'Feature cards' },
            steps:            { icon: 'bi-list-ol',                     color: '#5f7174', label: 'Steps' },
            quote_list:       { icon: 'bi-chat-quote',                  color: '#5f7174', label: 'Testimonials' },
            contact_info:     { icon: 'bi-telephone',                   color: '#5f7174', label: 'Contact details' },
            hero:             { icon: 'bi-star-fill',                   color: '#5f7174', label: 'Hero' },
            hero2:            { icon: 'bi-layout-text-window-reverse',  color: '#5f7174', label: 'Hero 2' },
            hero3:            { icon: 'bi-badge-hd',                    color: '#5f7174', label: 'Hero 3' },
            hero4:            { icon: 'bi-rocket-takeoff',              color: '#5f7174', label: 'Hero 4' },
            program_overview: { icon: 'bi-journal-bookmark',            color: '#5f7174', label: 'Programme Overview' },
            overview:         { icon: 'bi-grid-3x3-gap',                color: '#5f7174', label: 'Overview' },
            overview2:        { icon: 'bi-collection',                  color: '#5f7174', label: 'Overview 2' },
            about:            { icon: 'bi-people',                      color: '#5f7174', label: 'About' },
            about2:           { icon: 'bi-person-lines-fill',           color: '#5f7174', label: 'About 2' },
            sections:         { icon: 'bi-layout-three-columns',        color: '#5f7174', label: 'Sections' },
            sections2:        { icon: 'bi-layout-wtf',                  color: '#5f7174', label: 'Sections 2' },
            marquees:         { icon: 'bi-arrows-move',                 color: '#5f7174', label: 'Marquee' },
            logo_slider:      { icon: 'bi-images',                      color: '#5f7174', label: 'Logo Slider' },
            gallary:          { icon: 'bi-camera',                      color: '#5f7174', label: 'Gallery' },
            testimonial:      { icon: 'bi-chat-quote',                  color: '#5f7174', label: 'Testimonial' },
            faq:              { icon: 'bi-question-circle',             color: '#5f7174', label: 'FAQ' },
            closing_cta:      { icon: 'bi-megaphone',                   color: '#5f7174', label: 'Closing CTA' },
            form_dark:        { icon: 'bi-ui-checks',                   color: '#5f7174', label: 'Form (Dark)' },
            form_light:       { icon: 'bi-ui-checks-grid',              color: '#5f7174', label: 'Form (Light)' },
        };
        const BLOCK_DESCRIPTIONS = {
            page_header:      'The top of a page: small label, headline, intro, buttons and an optional image.',
            rich_text:        'Headings, paragraphs, lists, quotes and links. Write like in a document.',
            stats:            'A row of big numbers with labels, such as attendees, speakers or years.',
            form:             'A form you built in Forms, with a heading and intro. Responses go to your inbox.',
            events_grid:      'A list of your events. Choose upcoming or past, and filter by type.',
            event_facts:      'Date, place and type of the event this page is linked to.',
            cta_band:         'A bold closing band with a headline and one or two buttons.',
            faq_list:         'Common questions that open and close, in the same style as the rest of the page.',
            feature_grid:     'A grid of cards for services, benefits or what is included. Each can link somewhere.',
            steps:            'A numbered process, such as how it works or what happens after you get in touch.',
            quote_list:       'What clients or participants say about you, with their name and role.',
            contact_info:     'Email, phone, address and opening hours, each as a tappable line.',
            hero:             'Full-width hero with headline, CTA buttons, and a large banner image.',
            hero2:            'Split-layout hero with image on one side and text on the other.',
            hero3:            'Compact announcement banner with badge icon and verified marker.',
            hero4:            'Large hero with kicker text, subtitle, and dual CTA buttons.',
            program_overview: 'Programme highlights with icons and optional action links.',
            overview:         'Feature cards or timeline view for key points and benefits.',
            overview2:        'Icon grid for programme includes or deliverables.',
            about:            'About section with a large banner image, stat tiles, and feature cards.',
            about2:           'Split about section with two detail columns, image, and CTA.',
            sections:         'Alternating content sections with images and descriptions.',
            sections2:        'Card grid layout for services or topic sections.',
            marquees:         'Horizontal scrolling marquee of images or text.',
            logo_slider:      'Infinite logo/brand slider strip.',
            gallary:          'Photo gallery in grid or masonry layout.',
            testimonial:      'Testimonials in slider, grid, or featured card layout.',
            faq:              'Accordion or tabbed FAQ section.',
            closing_cta:      'Bottom-of-page call-to-action with one or more buttons.',
            form_dark:        'Contact/lead capture form on a dark background.',
            form_light:       'Contact/lead capture form on a light background.',
        };
        const APP_ORIGIN = @json(rtrim(url('/'), '/'));
        const PUBLIC_ROOT = STORAGE_ROOT + '/';
        const INTERNAL_ROUTES = @json($internalRoutes ?? []);
        const ROUTE_PARAM_OPTIONS = @json($routeBindingOptions ?? []);
        const ICON_OPTIONS = @json($iconOptions ?? []);


        /* ====== LIVE PREVIEW ====== */
        function pbRefreshPreview() {
            const f = document.getElementById('previewFrame');
            if (!f || !f.contentWindow) return;
            let y = 0;
            try { y = f.contentWindow.scrollY; } catch (e) {}
            f.addEventListener('load', () => { try { f.contentWindow.scrollTo(0, y); } catch (e) {} }, { once: true });
            try { f.contentWindow.location.reload(); } catch (e) { f.src = f.src; }
        }
        function pbSelectInPreview(id) {
            const f = document.getElementById('previewFrame');
            try { f.contentWindow.postMessage({ pbSelect: id }, window.location.origin); } catch (e) {}
        }
        window.addEventListener('message', (e) => {
            if (e.origin !== window.location.origin || !e.data || e.data.pbEdit === undefined) return;
            const row = document.querySelector(`.block-row[data-id="${e.data.pbEdit}"]`);
            if (!row) return;
            row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            row.querySelector('[data-action="edit"]')?.click();
        });
        document.addEventListener('click', (e) => {
            const edit = e.target.closest('.block-row [data-action="edit"]');
            if (edit) pbSelectInPreview(parseInt(edit.closest('.block-row').dataset.id, 10));
            const dev = e.target.closest('[data-device]');
            if (dev) {
                document.querySelectorAll('[data-device]').forEach(b => { b.classList.toggle('is-active', b === dev); b.setAttribute('aria-pressed', b === dev ? 'true' : 'false'); });
                document.getElementById('previewFrame').style.width = dev.dataset.device;
            }
            if (e.target.closest('#pbReload')) pbRefreshPreview();
        });

        /* ====== UTILITIES ====== */
        function html(markup) {
            const t = document.createElement('template');
            t.innerHTML = markup.trim();
            return t.content.firstElementChild;
        }

        function buildIconOptions() {
            if (!Array.isArray(ICON_OPTIONS) || ICON_OPTIONS.length === 0) {
                return '<option value="">Auto (based on text)</option>';
            }

            return [
                '<option value="">Auto (based on text)</option>',
                ...ICON_OPTIONS.map(opt => `<option value="${opt.value}">${opt.label}</option>`)
            ].join('');
        }

        function initIconPickers(scope = document) {
            scope.querySelectorAll('[data-icon-select]').forEach(select => {
                if (!select.dataset.iconOptionsLoaded) {
                    select.innerHTML = buildIconOptions();
                    select.dataset.iconOptionsLoaded = '1';
                }

                if (select.dataset.iconReady) {
                    return;
                }

                select.dataset.iconReady = '1';

                if (window.jQuery && window.jQuery.fn.select2) {
                    jQuery(select).select2({
                        dropdownParent: jQuery('#blockPanel'),
                        placeholder: 'Search icon',
                        allowClear: true,
                        width: '100%',
                    });
                }
            });
        }

        function syncIconPickers(scope = document) {
            if (!window.jQuery || !window.jQuery.fn.select2) return;
            scope.querySelectorAll('[data-icon-select]').forEach(select => {
                if (jQuery(select).data('select2')) {
                    jQuery(select).trigger('change.select2');
                }
            });
        }

        function linkControl(name, label = 'Link', wrapperClass = 'col-12', placeholder = 'https://example.com') {
            return `
<div class="${wrapperClass}">
  <label class="form-label small-label d-flex justify-content-between align-items-center">
    ${label}
    <span class="badge bg-light text-dark text-uppercase fw-normal">Link Picker</span>
  </label>
  <div class="link-picker border rounded-12 p-3" data-link-picker data-name="${name}">
    <div class="row g-2 align-items-center mb-2">
      <div class="col-12">
        <select class="form-select form-select-sm" data-link-picker-mode aria-label="Link source">
          <option value="external" selected>External URL</option>
          <option value="internal">Internal page</option>
        </select>
      </div>
    </div>
    <div data-link-picker-external>
      <input type="text" inputmode="url" autocapitalize="none" spellcheck="false" class="form-control" placeholder="${placeholder}">
    </div>
    <div data-link-picker-internal class="d-none mt-2">
      <select class="form-select" data-link-picker-route>
        <option value="">Select route</option>
      </select>
      <small class="text-muted d-block mt-1" data-link-picker-hint>
        Route placeholders like {event} still need an actual value.
      </small>
    </div>
    <div data-link-picker-params class="mt-2 d-none">
      <div class="row g-2" data-link-picker-params-fields></div>
      <small class="text-muted d-block mt-1">Provide values for route parameters.</small>
    </div>
    <input type="hidden" name="${name}">
  </div>
</div>`;
        }









        function slugify(str) {
            return (str || '').toString().normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .replace(/-{2,}/g, '-');
        }

        document.addEventListener('input', (e) => {
            if (e.target.matches('[data-field-label]')) {
                const wrap = e.target.closest('[data-repeater-item]');
                const nameInput = wrap?.querySelector('[data-field-name]');
                if (nameInput) {
                    const shouldSync = !nameInput.value.trim() || nameInput.dataset.autogenerated === '1';
                    if (shouldSync) {
                        const slug = slugify(e.target.value || '').replace(/-/g, '_');
                        nameInput.value = slug;
                        nameInput.dataset.autogenerated = slug ? '1' : '0';
                    }
                }
            }

            if (e.target.matches('[data-field-name]')) {
                const cleaned = slugify(e.target.value || '').replace(/-/g, '_');
                if (cleaned && cleaned !== e.target.value) {
                    e.target.value = cleaned;
                }
                e.target.dataset.autogenerated = '0';
            }
        });

        function getCSRF() {
            return document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value || '';
        }

        function setVal(name, val, scope = document) {
            const el = scope.querySelector(`[name='${CSS.escape(name)}']`);
            if (!el || el.type === 'file') return;
            if (el.type === 'checkbox') {
                el.checked = !!val;
            } else {
                el.value = val ?? '';
            }
        }

        /* ====== FILE PREVIEW HANDLER ====== */
        function setupFilePreviewHandlers(scope) {
            // Universal file change handler
            scope.addEventListener('change', (e) => {
                const input = e.target;
                if (input.type !== 'file' || !input.files?.[0]) return;

                const key = input.getAttribute('data-file-key');
                if (!key) return;

                const img = scope.querySelector(`img[data-preview-key="${key}"]`);
                if (img) {
                    img.src = URL.createObjectURL(input.files[0]);
                    img.style.display = 'block';
                }
            });
        }

        function isAbsoluteUrl(value = '') {
            return /^https?:\/\//i.test(value);
        }

        function resolveImageUrl(value = '') {
            if (!value) return null;
            if (isAbsoluteUrl(value)) return value;

            if (value.startsWith('//')) {
                return window.location.protocol + value;
            }

            if (value.startsWith('/')) {
                return `${APP_ORIGIN}${value}`;
            }

            const trimmed = value.replace(/^\/+/, '');
            if (trimmed.startsWith('storage/')) {
                return `${APP_ORIGIN}/${trimmed}`;
            }

            return `${STORAGE_ROOT}/${trimmed}`;
        }

        function showExistingImages(scope, publicRoot, data, prefix = '') {
            for (const [key, val] of Object.entries(data)) {
                const fullKey = prefix ? `${prefix}_${key}` : key;

                if (typeof val === 'string' && (key === 'image' || key === 'photo' || key === 'bg' ||
                        key.includes('_image') || key === 'banner_image' || key === 'hero_image' || key === 'about_image' || key === 'verified_icon'
                    )) {
                    const img = scope.querySelector(`img[data-preview-key="${fullKey}"]`);
                    if (img && val) {
                        const src = resolveImageUrl(val);
                        if (src) {
                            img.src = src;
                        }
                        img.style.display = 'block';
                    }
                } else if (Array.isArray(val)) {
                    val.forEach((item, i) => {
                        if (typeof item === 'object') {
                            showExistingImages(scope, publicRoot, item, `${fullKey}_${i}`);
                        }
                    });
                } else if (typeof val === 'object' && val !== null) {
                    showExistingImages(scope, publicRoot, val, fullKey);
                }
            }
        }

        function refreshExistingImagePreviews(scope, data) {
            if (!scope || !data || typeof data !== 'object') return;
            showExistingImages(scope, PUBLIC_ROOT, data);
        }

        function escapeHtml(str = '') {
            return (str ?? '').toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        const ROUTE_OPTIONS_HTML = ['<option value="">Select route</option>'].concat(
            INTERNAL_ROUTES.map(route => {
                const hint = route.needs_params ? ' (needs params)' : '';
                const label = `${escapeHtml(route.name)} • ${escapeHtml(route.path)}`;
                const attrs = `value="${escapeHtml(route.path)}" data-uri="${escapeHtml(route.path)}" data-needs="${route.needs_params ? 1 : 0}"`;
                return `<option ${attrs}>${label}${hint}</option>`;
            })
        ).join('');

        function findRouteByPath(path) {
            return INTERNAL_ROUTES.find(route => route.path === path);
        }

        function escapeRegex(str) {
            return str.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&');
        }

        function buildRegexForRoute(route) {
            if (!route || !route.needs_params) {
                return new RegExp('^' + escapeRegex(route?.path || '') + '$');
            }

            let pattern = escapeRegex(route.path);
            (route.placeholders || []).forEach(ph => {
                const token = escapeRegex(`{${ph}}`);
                pattern = pattern.replace(new RegExp(token, 'g'), '([^/]+)');
            });
            return new RegExp('^' + pattern + '$');
        }

        function buildPathFromParams(route, params = {}) {
            if (!route) return '';
            let path = route.path;
            (route.placeholders || []).forEach(ph => {
                path = path.replace(`{${ph}}`, params[ph] ?? '');
            });
            return path;
        }

        function normalizeInternalLink(value = '') {
            if (!value) return '';

            const originless = value.startsWith(APP_ORIGIN)
                ? value.slice(APP_ORIGIN.length)
                : value;

            try {
                const parsed = new URL(value);
                if (parsed.origin === APP_ORIGIN) {
                    return parsed.pathname + parsed.search;
                }
            } catch (e) {
                // not an absolute URL, fall through
            }

            return originless || '/';
        }

        function matchRouteFromValue(value) {
            if (!value) return null;

            const normalizedValue = normalizeInternalLink(value);

            for (const route of INTERNAL_ROUTES) {
                if (!route.needs_params && route.path === normalizedValue) {
                    return { route, params: {} };
                }

                if (route.needs_params) {
                    const regex = buildRegexForRoute(route);
                    const match = normalizedValue.match(regex);
                    if (match) {
                        const params = {};
                        (route.placeholders || []).forEach((ph, idx) => {
                            params[ph] = match[idx + 1] || '';
                        });
                        return { route, params };
                    }
                }
            }

            return null;
        }

        function hydrateLinkPicker(picker) {
            const hidden = picker.querySelector('input[type="hidden"][name]');
            const modeSelect = picker.querySelector('[data-link-picker-mode]');
            const externalWrap = picker.querySelector('[data-link-picker-external]');
            const internalWrap = picker.querySelector('[data-link-picker-internal]');
            const paramsWrap = picker.querySelector('[data-link-picker-params]');
            const paramsFields = picker.querySelector('[data-link-picker-params-fields]');
            const externalInput = externalWrap?.querySelector('input');
            const routeSelect = picker.querySelector('[data-link-picker-route]');
            const hint = picker.querySelector('[data-link-picker-hint]');

            if (!hidden || !modeSelect) return;

            if (routeSelect && !routeSelect.dataset.routesLoaded) {
                routeSelect.innerHTML = ROUTE_OPTIONS_HTML;
                routeSelect.dataset.routesLoaded = '1';
            }

            const getSelectedRoute = () => findRouteByPath(routeSelect?.value || '');

            const collectParams = () => {
                const params = {};
                paramsFields?.querySelectorAll('[data-param-name]').forEach(input => {
                    params[input.dataset.paramName] = input.value.trim();
                });
                return params;
            };

            const updateRouteHint = (route) => {
                if (!hint) return;
                if (!route) {
                    hint.textContent = 'Route placeholders like {event} still need an actual value.';
                    return;
                }
                const placeholders = route.placeholders || [];
                hint.textContent = route.needs_params
                    ? `Route path: ${route.path} • fill ${placeholders.join(', ')}`
                    : `Route path: ${route.path}`;
            };

            const syncHidden = () => {
                if (!hidden) return;
                if (modeSelect?.value === 'internal') {
                    const route = getSelectedRoute();
                    if (!route) {
                        hidden.value = '';
                        return;
                    }
                    if (route.needs_params) {
                        const params = collectParams();
                        const ready = (route.placeholders || []).every(ph => params[ph]);
                        hidden.value = ready ? buildPathFromParams(route, params) : '';
                    } else {
                        hidden.value = route.path;
                    }
                } else {
                    hidden.value = externalInput?.value?.trim() || '';
                }
            };

            function formatParamLabel(param) {
                return (param || '')
                    .split(/[_-]/g)
                    .filter(Boolean)
                    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
                    .join(' ') || 'Parameter';
            }

            function renderParamInputs(route, values = {}) {
                if (!paramsWrap || !paramsFields) return;
                if (!route || !route.needs_params) {
                    paramsWrap.classList.add('d-none');
                    paramsFields.innerHTML = '';
                    return;
                }

                paramsWrap.classList.remove('d-none');
                paramsFields.innerHTML = (route.placeholders || []).map(ph => {
                    const choices = (ROUTE_PARAM_OPTIONS && ROUTE_PARAM_OPTIONS[ph]) ? ROUTE_PARAM_OPTIONS[ph] : [];
                    const label = formatParamLabel(ph);
                    const safeLabel = escapeHtml(label);
                    const safeParamName = escapeHtml(ph);

                    if (choices.length) {
                        const options = ['<option value="">' + escapeHtml('Select ' + label) + '</option>'].concat(
                            choices.map(opt => {
                                const optionLabel = escapeHtml(opt.label || opt.value || '');
                                const extra = opt.hint ? ' • ' + escapeHtml(opt.hint) : '';
                                return `<option value="${escapeHtml(opt.value ?? '')}">${optionLabel}${extra}</option>`;
                            })
                        ).join('');

                        return `
  <div class="col-md-6">
    <label class="form-label small-label mb-0">${safeLabel}</label>
    <select class="form-select form-select-sm" data-param-name="${safeParamName}">
      ${options}
    </select>
  </div>`;
                    }

                    return `
  <div class="col-md-6">
    <label class="form-label small-label mb-0">${safeLabel}</label>
    <input type="text" class="form-control form-control-sm" data-param-name="${safeParamName}">
  </div>`;
                }).join('');

                paramsFields.querySelectorAll('[data-param-name]').forEach(input => {
                    const param = input.dataset.paramName;
                    if (param && Object.prototype.hasOwnProperty.call(values, param)) {
                        input.value = values[param] ?? '';
                    }
                    const eventName = input.tagName === 'SELECT' ? 'change' : 'input';
                    input.addEventListener(eventName, syncHidden);
                });
            }

            const handleModeChange = () => {
                const internal = modeSelect.value === 'internal';
                externalWrap?.classList.toggle('d-none', internal);
                internalWrap?.classList.toggle('d-none', !internal);
                if (!internal) {
                    renderParamInputs(null);
                } else {
                    renderParamInputs(getSelectedRoute());
                }
                updateRouteHint(getSelectedRoute());
                syncHidden();
            };

            const handleRouteChange = (params = {}) => {
                const route = getSelectedRoute();
                renderParamInputs(route, params);
                updateRouteHint(route);
                syncHidden();
            };

            if (!picker.dataset.linkPickerReady) {
                modeSelect?.addEventListener('change', handleModeChange);
                externalInput?.addEventListener('input', syncHidden);
                routeSelect?.addEventListener('change', () => handleRouteChange({}));
                picker.dataset.linkPickerReady = '1';
            }

            const current = hidden.value || '';
            const matched = matchRouteFromValue(current);

            if (matched && routeSelect) {
                modeSelect.value = 'internal';
                externalWrap?.classList.add('d-none');
                internalWrap?.classList.remove('d-none');
                routeSelect.value = matched.route.path;
                handleRouteChange(matched.params);
                hidden.value = buildPathFromParams(matched.route, matched.params);
            } else {
                modeSelect.value = 'external';
                externalWrap?.classList.remove('d-none');
                internalWrap?.classList.add('d-none');
                renderParamInputs(null);
                updateRouteHint(null);
                if (externalInput) {
                    externalInput.value = current;
                }
            }

            syncHidden();
        }

        function initLinkPickers(scope = document) {
            scope.querySelectorAll('[data-link-picker]').forEach(hydrateLinkPicker);
        }

        /* ====== ADD BLOCK BAR (visual picker) ====== */

        function updateVariantSelect(type, selectEl) {
            if (!selectEl) return;
            const opts = VARIANTS[type] || ['default'];
            selectEl.innerHTML = '<option value="">Default</option>' +
                opts.filter(o => o !== 'default')
                .map(o => `<option value="${o}">${o}</option>`).join('');
        }

        function buildBlockPicker() {
            const grid = document.getElementById('pickerGrid');
            if (!grid || grid.dataset.built) return;
            grid.dataset.built = '1';

            const addHeading = (text) => {
                const h = document.createElement('div');
                h.className = 'col-12 pb-picker-heading';
                h.textContent = text;
                grid.appendChild(h);
            };
            const entries = Object.entries(BLOCK_TYPE_META).filter(([type]) => PICKER_TYPES.includes(type));
            let lastGroup = null;
            entries.forEach(([type, meta]) => {
                const group = NEW_TYPES.includes(type) ? 'Build your page' : 'Classic sections';
                if (group !== lastGroup) { addHeading(group); lastGroup = group; }
                const variants = VARIANTS[type] || ['default'];
                const col = document.createElement('div');
                col.className = 'col-md-4 col-lg-3';
                col.dataset.blockLabel = meta.label.toLowerCase();
                col.innerHTML = `
                  <div class="border rounded-3 p-3 h-100 d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-2">
                      <span class="rounded-2 d-inline-flex align-items-center justify-content-center flex-shrink-0"
                            style="background:${meta.color}22;color:${meta.color};width:32px;height:32px">
                        <i class="bi ${meta.icon}"></i>
                      </span>
                      <strong class="text-capitalize">${meta.label}</strong>
                    </div>
                    <p class="small text-muted mb-0 flex-grow-1">${BLOCK_DESCRIPTIONS[type] || ''}</p>
                    <div class="d-flex flex-wrap gap-1">
                      ${variants.map(v => `<button type="button" class="btn btn-sm btn-outline-secondary picker-btn"
                        data-type="${type}" data-variant="${v === 'default' ? '' : v}">${v}</button>`).join('')}
                    </div>
                  </div>`;
                grid.appendChild(col);
            });

            grid.addEventListener('click', e => {
                const btn = e.target.closest('.picker-btn');
                if (!btn) return;
                const pickerModal = bootstrap.Modal.getInstance(document.getElementById('blockPickerModal'));
                pickerModal?.hide();
                setTimeout(() => openModal('create', { type: btn.dataset.type, variant: btn.dataset.variant }), 300);
            });

            document.getElementById('pickerSearch')?.addEventListener('input', e => {
                const q = e.target.value.toLowerCase();
                grid.querySelectorAll('.pb-picker-heading').forEach(h => { h.style.display = q ? 'none' : ''; });
                grid.querySelectorAll('[data-block-label]').forEach(col => {
                    col.style.display = col.dataset.blockLabel.includes(q) ? '' : 'none';
                });
            });
        }

        document.getElementById('openBlockPickerBtn')?.addEventListener('click', () => {
            buildBlockPicker();
            new bootstrap.Modal(document.getElementById('blockPickerModal')).show();
        });

        /* ====== OFFCANVAS PANEL ====== */
        let bsModal;

        function ensureModal() {
            if (!bsModal) bsModal = new bootstrap.Offcanvas(document.getElementById('blockPanel'));
            return bsModal;
        }

        function openModal(mode, data) {
            const modal = ensureModal();
            const form = document.getElementById('modalForm');
            const titleEl = document.getElementById('blockPanelLabel');
            const typeSel = document.getElementById('modal_type');
            const varSel = document.getElementById('modal_variant');
            const pubSel = document.getElementById('modal_published');
            const mount = document.getElementById('fieldMount');

            // CSRF
            if (!form.querySelector('input[name="_token"]')) {
                const tok = document.createElement('input');
                tok.type = 'hidden';
                tok.name = '_token';
                tok.value = getCSRF();
                form.appendChild(tok);
            }

            // Setup action
            if (mode === 'create') {
                form.action = @json(route('pb.blocks.store', $page));
                form.querySelector('input[name="_method"]')?.remove();
                if (titleEl) titleEl.textContent = 'Add Block';
            } else {
                form.action = @json(route('pb.blocks.update', ':id')).replace(':id', data.id);
                let m = form.querySelector('input[name="_method"]');
                if (!m) {
                    m = document.createElement('input');
                    m.type = 'hidden';
                    m.name = '_method';
                    form.appendChild(m);
                }
                m.value = 'PUT';
                if (titleEl) titleEl.textContent = 'Edit Block';
            }

            // Set selects
            typeSel.value = data.type || '';
            updateVariantSelect(typeSel.value, varSel);
            varSel.value = data.variant || '';
            pubSel.value = data.is_published === false || data.is_published === 0 ? '0' : '1';

            // Render fields
            mount.innerHTML = '';
            let dataPayload = data.data && typeof data.data === 'object' ? { ...data.data } : {};
            if (typeSel.value === 'hero3' && dataPayload) {
                if (!dataPayload.title && Array.isArray(dataPayload.title_segments)) {
                    dataPayload.title = dataPayload.title_segments.join(' ');
                }
                if (!dataPayload.description && typeof dataPayload.sub_text === 'string') {
                    dataPayload.description = dataPayload.sub_text;
                }
            }

            const fieldsEl = renderFieldsForType(typeSel.value);
            if (fieldsEl) {
                mount.appendChild(fieldsEl);

                // 1. Initialize repeaters
                initRepeater(mount);
                initLinkPickers(mount);
                initIconPickers(mount);

                // 2. Hydrate after two animation frames (DOM fully painted, no fixed-ms fragility)
                if (dataPayload && typeof dataPayload === 'object') {
                    requestAnimationFrame(() => requestAnimationFrame(() => {
                        hydrateFields(mount, dataPayload);
                        initLinkPickers(mount);
                        initIconPickers(mount);
                        syncIconPickers(mount);
                        refreshExistingImagePreviews(mount, data.data || {});
                    }));
                }

                // 3. Setup file previews
                setupFilePreviewHandlers(mount);

                // 4. Type-specific bindings
                attachTypeBindings(typeSel.value, mount, mode, data.data || {});
            }

            // Type change handler
            typeSel.onchange = () => {
                updateVariantSelect(typeSel.value, varSel);
                mount.innerHTML = '';
                const newFields = renderFieldsForType(typeSel.value);
                if (newFields) {
                    mount.appendChild(newFields);
                    initRepeater(mount);
                    initLinkPickers(mount);
                    initIconPickers(mount);
                    setupFilePreviewHandlers(mount);
                    attachTypeBindings(typeSel.value, mount, 'create', {});
                }
            };

            // Form submit handler — AJAX with iziToast feedback
            if (!form.dataset.finalizeHooked) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const mount = document.getElementById('fieldMount');
                    pruneEmptyRepeaterItems(mount);
                    pruneEmptyListInputs(mount);
                    finalizeRepeaters(mount);
                    stripLeftoverPlaceholders(mount);

                    const submitBtn = document.getElementById('modalSubmit');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Saving…';

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': getCSRF(),
                            },
                            body: new FormData(form),
                            credentials: 'same-origin',
                        });

                        const json = await res.json().catch(() => null);

                        if (res.ok && json?.ok) {
                            iziToast.success({
                                title: 'Saved',
                                message: json.message || 'Block saved.',
                                position: 'topRight',
                                timeout: 3500,
                            });
                            bsModal?.hide();
                            if (json.block) {
                                updateBlockCard(json.block);
                            }
                            pbRefreshPreview();
                        } else {
                            const msgs = json?.errors
                                ? Object.values(json.errors).flat().join(' ')
                                : (json?.message || 'An error occurred while saving.');
                            iziToast.error({
                                title: 'Error',
                                message: msgs,
                                position: 'topRight',
                                timeout: 6000,
                            });
                        }
                    } catch (err) {
                        iziToast.error({
                            title: 'Network Error',
                            message: 'Could not reach the server. Please try again.',
                            position: 'topRight',
                            timeout: 5000,
                        });
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save';
                    }
                });
                form.dataset.finalizeHooked = '1';
            }

            modal.show();
        }

        function renderFormBuilderFields() {
            return html(`
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label small-label">Section Title</label>
    <input class="form-control" name="data[title]" placeholder="Let’s build something great">
  </div>
  <div class="col-md-6">
    <label class="form-label small-label">Subtitle</label>
    <input class="form-control" name="data[subtitle]" placeholder="Share a short intro">
  </div>
  <div class="col-md-4">
    <label class="form-label small-label">Submit Button Text</label>
    <input class="form-control" name="data[button_text]" placeholder="Join the newsletter">
  </div>
  <div class="col-md-4">
    <label class="form-label small-label">Success Tags (comma separated)</label>
    <input class="form-control" name="data[tags]" placeholder="Newsletter, Website">
  </div>
  <div class="col-md-4">
    <label class="form-label small-label">Auto Reply Type</label>
    <select class="form-select" name="data[email_mode]">
      <option value="newsletter">Newsletter welcome</option>
      <option value="thank_you">Thank you note</option>
      <option value="custom">Custom message</option>
      <option value="none">No auto-reply</option>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label small-label">Auto Reply Subject</label>
    <input class="form-control" name="data[email_subject]" placeholder="Thanks for reaching out">
  </div>
  <div class="col-12">
    <label class="form-label small-label">Auto Reply Message</label>
    <textarea class="form-control" name="data[email_body]" rows="4" placeholder="Compose the email body your leads should receive. Supports simple HTML."></textarea>
  </div>

  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Fields</span></div></div>
  <div class="col-12 d-flex justify-content-between align-items-center">
    <label class="form-label small-label mb-0">Form Fields</label>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-repeater-add="fields">Add Field</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="fields" data-max="10">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-5">
                <label class="form-label small-label">Label*</label>
                <input class="form-control" name="data[fields][__INDEX__][label]" required data-field-label>
              </div>
              <div class="col-md-4">
                <label class="form-label small-label">Field Name*</label>
                <input class="form-control" name="data[fields][__INDEX__][name]" required data-field-name>
              </div>
              <div class="col-md-3">
                <label class="form-label small-label">Type</label>
                <select class="form-select" name="data[fields][__INDEX__][type]">
                  <option value="text">Text</option>
                  <option value="email">Email</option>
                  <option value="tel">Phone</option>
                  <option value="textarea">Textarea</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small-label">Placeholder</label>
                <input class="form-control" name="data[fields][__INDEX__][placeholder]" placeholder="Type here...">
              </div>
              <div class="col-md-3">
                <label class="form-label small-label">Required?</label>
                <select class="form-select" name="data[fields][__INDEX__][required]">
                  <option value="1">Yes</option>
                  <option value="0" selected>No</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small-label">Width</label>
                <select class="form-select" name="data[fields][__INDEX__][width]">
                  <option value="full">Full</option>
                  <option value="half">Half</option>
                </select>
              </div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);
        }


        /* ====== FIELD RENDERERS ====== */
        function buildOverviewFields() {
            return html(`
<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label small-label">Kicker</label>
    <input class="form-control" name="data[kicker]">
  </div>
  <div class="col-md-8">
    <label class="form-label small-label">Title*</label>
    <input class="form-control" name="data[title]" required>
  </div>
  <div class="col-12">
    <label class="form-label small-label">Description</label>
    <textarea class="form-control" name="data[description]" rows="3"></textarea>
  </div>
  <div class="col-md-5">
    <label class="form-label small-label">CTA Text</label>
    <input class="form-control" name="data[link_text]">
  </div>
  ${linkControl('data[link]', 'CTA Link', 'col-md-7')}
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Cards</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Cards</label>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-repeater-add="items">Add Card</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items" data-max="5">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6">
                <label class="form-label small-label">Subtitle*</label>
                <input class="form-control" name="data[items][__INDEX__][subtitle]" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small-label">Link Text</label>
                <input class="form-control" name="data[items][__INDEX__][link_text]">
              </div>
              <div class="col-md-6">
                <label class="form-label small-label">Icon</label>
                <select class="form-select" data-icon-select name="data[items][__INDEX__][icon_bi]">
                  <option value="">Auto (based on text)</option>
                </select>
                <small class="text-muted">Search Bootstrap icon by name</small>
              </div>
              <div class="col-12">
                <label class="form-label small-label">Text</label>
                <textarea class="form-control" name="data[items][__INDEX__][text]" rows="2"></textarea>
              </div>
              ${linkControl('data[items][__INDEX__][link]', 'Link', 'col-12')}
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);
        }

        function renderFieldsForType(type) {
            switch (type) {



                case 'feature_grid':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="160"></div>
  <div class="col-md-6"><label class="form-label small-label">Background</label>
    <select class="form-select" name="data[tone]"><option value="white">White</option><option value="tint">Pale blue</option><option value="navy">Navy</option></select></div>
  <div class="col-md-6"><label class="form-label small-label">Cards per row</label>
    <select class="form-select" name="data[columns]"><option value="3">3</option><option value="2">2</option><option value="4">4</option></select></div>
  <div class="col-md-6"><label class="form-label small-label">Intro (optional)</label><input class="form-control" name="data[subtitle]" maxlength="320"></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Cards</label>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-repeater-add="items">Add card</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items" data-max="12">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle"><i class="bi bi-grip-vertical" aria-hidden="true"></i></span>
            <div class="row g-2 flex-grow-1">
              <div class="col-12"><label class="form-label small-label">Title*</label><input class="form-control" name="data[items][__INDEX__][title]" required maxlength="100"></div>
              <div class="col-12"><label class="form-label small-label">Text</label><textarea class="form-control" rows="2" name="data[items][__INDEX__][text]" maxlength="400"></textarea></div>
              <div class="col-md-6"><label class="form-label small-label">Link text (optional)</label><input class="form-control" name="data[items][__INDEX__][link_text]" maxlength="60"></div>
              <div class="col-md-6"><label class="form-label small-label">Link (optional)</label><input class="form-control" name="data[items][__INDEX__][link]" placeholder="/consultancy"></div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'steps':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="160"></div>
  <div class="col-md-6"><label class="form-label small-label">Background</label>
    <select class="form-select" name="data[tone]"><option value="white">White</option><option value="tint">Pale blue</option></select></div>
  <div class="col-12"><label class="form-label small-label">Intro (optional)</label><input class="form-control" name="data[subtitle]" maxlength="320"></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Steps</label>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-repeater-add="items">Add step</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items" data-max="8">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle"><i class="bi bi-grip-vertical" aria-hidden="true"></i></span>
            <div class="row g-2 flex-grow-1">
              <div class="col-12"><label class="form-label small-label">Title*</label><input class="form-control" name="data[items][__INDEX__][title]" required maxlength="100"></div>
              <div class="col-12"><label class="form-label small-label">Text</label><textarea class="form-control" rows="2" name="data[items][__INDEX__][text]" maxlength="400"></textarea></div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'quote_list':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="160"></div>
  <div class="col-md-6"><label class="form-label small-label">Background</label>
    <select class="form-select" name="data[tone]"><option value="white">White</option><option value="tint">Pale blue</option></select></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Testimonials</label>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-repeater-add="items">Add testimonial</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items" data-max="6">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle"><i class="bi bi-grip-vertical" aria-hidden="true"></i></span>
            <div class="row g-2 flex-grow-1">
              <div class="col-12"><label class="form-label small-label">Quote*</label><textarea class="form-control" rows="3" name="data[items][__INDEX__][quote]" required maxlength="600"></textarea></div>
              <div class="col-md-6"><label class="form-label small-label">Name*</label><input class="form-control" name="data[items][__INDEX__][name]" required maxlength="80"></div>
              <div class="col-md-6"><label class="form-label small-label">Role or organisation</label><input class="form-control" name="data[items][__INDEX__][role]" maxlength="120"></div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'contact_info':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="160"></div>
  <div class="col-md-6"><label class="form-label small-label">Background</label>
    <select class="form-select" name="data[tone]"><option value="white">White</option><option value="tint">Pale blue</option></select></div>
  <div class="col-12"><label class="form-label small-label">Intro (optional)</label><input class="form-control" name="data[subtitle]" maxlength="320"></div>
  <div class="col-md-6"><label class="form-label small-label">Email</label><input class="form-control" type="email" name="data[email]" maxlength="150"></div>
  <div class="col-md-6"><label class="form-label small-label">Phone</label><input class="form-control" name="data[phone]" maxlength="40"></div>
  <div class="col-md-6"><label class="form-label small-label">Address</label><textarea class="form-control" rows="2" name="data[address]" maxlength="300"></textarea></div>
  <div class="col-md-6"><label class="form-label small-label">Opening hours</label><textarea class="form-control" rows="2" name="data[hours]" maxlength="200" placeholder="Mon to Fri, 9am to 5pm"></textarea></div>
</div>`);

                case 'faq_list':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="160"></div>
  <div class="col-md-6"><label class="form-label small-label">Background</label>
    <select class="form-select" name="data[tone]"><option value="white">White</option><option value="tint">Pale blue</option></select></div>
  <div class="col-12"><label class="form-label small-label">Intro (optional)</label><input class="form-control" name="data[subtitle]" maxlength="320"></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Questions</label>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-repeater-add="items">Add question</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items" data-max="50">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle"><i class="bi bi-grip-vertical" aria-hidden="true"></i></span>
            <div class="row g-2 flex-grow-1">
              <div class="col-12"><label class="form-label small-label">Question*</label><input class="form-control" name="data[items][__INDEX__][q]" required maxlength="200"></div>
              <div class="col-12"><label class="form-label small-label">Answer*</label><textarea class="form-control" rows="3" name="data[items][__INDEX__][a]" required maxlength="1200"></textarea></div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'page_header':
                    return html(`
<div class="row g-3">
  <div class="col-md-4"><label class="form-label small-label">Small label above the headline</label><input class="form-control" name="data[kicker]" maxlength="80"></div>
  <div class="col-md-8"><label class="form-label small-label">Headline*</label><input class="form-control" name="data[title]" required maxlength="180"></div>
  <div class="col-12"><label class="form-label small-label">Intro</label><textarea class="form-control" rows="3" name="data[subtitle]" maxlength="400"></textarea></div>
  <div class="col-md-6"><label class="form-label small-label">Main button text</label><input class="form-control" name="data[primary_text]" maxlength="60"></div>
  ${linkControl('data[primary_link]', 'Main button link', 'col-md-6')}
  <div class="col-md-6"><label class="form-label small-label">Second link text</label><input class="form-control" name="data[secondary_text]" maxlength="60"></div>
  ${linkControl('data[secondary_link]', 'Second link', 'col-md-6')}
  <div class="col-md-6">
    <label class="form-label small-label">Image (optional)</label>
    <input class="form-control" type="file" name="image" accept=".webp,.jpg,.jpeg,.png" data-file-key="image">
    <div class="mt-2"><img data-preview-key="image" style="max-width:100%;height:auto;display:none"></div>
  </div>
  <div class="col-md-6"><label class="form-label small-label">Image description (for screen readers)</label><input class="form-control" name="data[image_alt]" maxlength="160"></div>
</div>`);

                case 'rich_text':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="180"></div>
  <div class="col-md-3"><label class="form-label small-label">Width</label>
    <select class="form-select" name="data[width]"><option value="narrow">Narrow, easy to read</option><option value="wide">Wide</option></select></div>
  <div class="col-md-3"><label class="form-label small-label">Background</label>
    <select class="form-select" name="data[tone]"><option value="white">White</option><option value="tint">Pale blue</option></select></div>
  <div class="col-12">
    <label class="form-label small-label" id="rtLabel">Text*</label>
    <div class="rt" data-rt>
      <div class="rt-bar" role="toolbar" aria-label="Text formatting">
        <button type="button" class="rt-btn" data-cmd="bold" aria-label="Bold" title="Bold"><i class="bi bi-type-bold" aria-hidden="true"></i></button>
        <button type="button" class="rt-btn" data-cmd="italic" aria-label="Italic" title="Italic"><i class="bi bi-type-italic" aria-hidden="true"></i></button>
        <span class="rt-sep"></span>
        <button type="button" class="rt-btn rt-txt" data-block="h2" title="Heading">H2</button>
        <button type="button" class="rt-btn rt-txt" data-block="h3" title="Subheading">H3</button>
        <button type="button" class="rt-btn rt-txt" data-block="p" title="Paragraph">Text</button>
        <span class="rt-sep"></span>
        <button type="button" class="rt-btn" data-cmd="insertUnorderedList" aria-label="Bulleted list" title="Bulleted list"><i class="bi bi-list-ul" aria-hidden="true"></i></button>
        <button type="button" class="rt-btn" data-cmd="insertOrderedList" aria-label="Numbered list" title="Numbered list"><i class="bi bi-list-ol" aria-hidden="true"></i></button>
        <button type="button" class="rt-btn" data-block="blockquote" aria-label="Quote" title="Quote"><i class="bi bi-quote" aria-hidden="true"></i></button>
        <span class="rt-sep"></span>
        <button type="button" class="rt-btn" data-link aria-label="Add link" title="Add link"><i class="bi bi-link-45deg" aria-hidden="true"></i></button>
        <span class="rt-linkbox" hidden>
          <input type="url" class="form-control form-control-sm" placeholder="https://" aria-label="Link address">
          <button type="button" class="btn btn-sm btn-primary" data-link-apply>Apply</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" data-link-remove>Remove</button>
        </span>
      </div>
      <div class="rt-area" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="rtLabel" data-rt-area></div>
      <input type="hidden" name="data[body]" data-rt-input>
    </div>
  </div>
</div>`);

                case 'stats':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="160"></div>
  <div class="col-md-6"><label class="form-label small-label">Background</label>
    <select class="form-select" name="data[tone]"><option value="white">White</option><option value="tint">Pale blue</option><option value="navy">Navy</option></select></div>
  <div class="col-12"><label class="form-label small-label">Intro (optional)</label><input class="form-control" name="data[subtitle]" maxlength="320"></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Numbers</label>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-repeater-add="items">Add number</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items" data-max="8">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle"><i class="bi bi-grip-vertical" aria-hidden="true"></i></span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-4"><label class="form-label small-label">Number*</label><input class="form-control" name="data[items][__INDEX__][value]" placeholder="120" required maxlength="24"></div>
              <div class="col-md-8"><label class="form-label small-label">Label*</label><input class="form-control" name="data[items][__INDEX__][label]" placeholder="Attendees" required maxlength="90"></div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'form':
                    return html(`
<div class="row g-3">
  <div class="col-12">
    <label class="form-label small-label">Form*</label>
    <select class="form-select" name="data[form_id]" required>
      <option value="">Choose a form…</option>
      ${FORM_OPTIONS.map(f => `<option value="${f.id}">${String(f.name).replace(/</g, '&lt;')}</option>`).join('')}
    </select>
    <div class="form-text">Build and edit forms under Forms in the sidebar. Responses go to Responses.</div>
  </div>
  <div class="col-md-8"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="160"></div>
  <div class="col-md-4"><label class="form-label small-label">Background</label>
    <select class="form-select" name="data[tone]"><option value="white">White</option><option value="tint">Pale blue</option><option value="navy">Navy</option></select></div>
  <div class="col-12"><label class="form-label small-label">Intro (optional)</label><textarea class="form-control" rows="2" name="data[intro]" maxlength="400"></textarea></div>
</div>`);

                case 'events_grid':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="160"></div>
  <div class="col-md-6"><label class="form-label small-label">Intro (optional)</label><input class="form-control" name="data[subtitle]" maxlength="320"></div>
  <div class="col-md-4"><label class="form-label small-label">Show*</label>
    <select class="form-select" name="data[show]" required><option value="upcoming">Upcoming events</option><option value="past">Past events</option><option value="all">All events</option></select></div>
  <div class="col-md-4"><label class="form-label small-label">Category</label>
    <select class="form-select" name="data[category_id]"><option value="">Any category</option>${Object.entries(EVENT_TYPES).map(([k, v]) => `<option value="${k}">${String(v).replace(/</g, '&lt;')}</option>`).join('')}</select></div>
  <div class="col-md-4"><label class="form-label small-label">How many</label><input class="form-control" type="number" min="1" max="12" name="data[limit]" placeholder="3"></div>
  <div class="col-md-6"><label class="form-label small-label">Link text</label><input class="form-control" name="data[link_text]" maxlength="60" placeholder="All events"></div>
  ${linkControl('data[link]', 'Link', 'col-md-6')}
</div>`);

                case 'event_facts':
                    return html(`
<div class="row g-3">
  <div class="col-12"><div class="alert alert-info mb-0">Shows the date, place and type of the event this page is linked to. Link the page under Page settings, Bind to Event.</div></div>
  <div class="col-md-6"><label class="form-label small-label">Heading (optional)</label><input class="form-control" name="data[title]" maxlength="120" placeholder="At a glance"></div>
  <div class="col-md-6"><label class="form-label small-label">Note (optional)</label><input class="form-control" name="data[note]" maxlength="300"></div>
  <div class="col-md-6"><label class="form-label small-label">Button text (hidden after the event)</label><input class="form-control" name="data[button_text]" maxlength="60"></div>
  ${linkControl('data[button_link]', 'Button link', 'col-md-6')}
</div>`);

                case 'cta_band':
                    return html(`
<div class="row g-3">
  <div class="col-12"><label class="form-label small-label">Headline*</label><input class="form-control" name="data[title]" required maxlength="200"></div>
  <div class="col-12"><label class="form-label small-label">Supporting text (optional)</label><textarea class="form-control" rows="2" name="data[subtitle]" maxlength="320"></textarea></div>
  <div class="col-md-6"><label class="form-label small-label">Main button text</label><input class="form-control" name="data[primary_text]" maxlength="60"></div>
  ${linkControl('data[primary_link]', 'Main button link', 'col-md-6')}
  <div class="col-md-6"><label class="form-label small-label">Second link text</label><input class="form-control" name="data[secondary_text]" maxlength="60"></div>
  ${linkControl('data[secondary_link]', 'Second link', 'col-md-6')}
</div>`);

                case 'hero':
                    return html(`
<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label small-label">Slug (auto)</label>
    <input class="form-control" name="data[slug]" data-hero-slug readonly>
  </div>
  <div class="col-md-8">
    <label class="form-label small-label">Title*</label>
    <input class="form-control" name="data[title]" required>
  </div>
  <div class="col-md-5">
    <label class="form-label small-label">Link Text</label>
    <input class="form-control" name="data[link_text]">
  </div>
  ${linkControl('data[link]', 'Link', 'col-md-7')}
  <div class="col-md-5">
    <label class="form-label small-label">Secondary Link Text</label>
    <input class="form-control" name="data[link_text_secondary]">
  </div>
  ${linkControl('data[link_secondary]', 'Secondary Link', 'col-md-7')}
  <div class="col-12">
    <label class="form-label small-label">Sub Text</label>
    <textarea class="form-control" name="data[sub_text]" rows="3"></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label small-label">Banner Image</label>
    <input class="form-control" type="file" name="banner_image" accept=".webp,.jpg,.jpeg,.png" data-file-key="banner_image">
    <div class="mt-2"><img data-preview-key="banner_image" style="max-width:100%;height:auto;display:none"></div>
  </div>
</div>`);

                case 'hero2':
                    return html(`
<div class="row g-3">
  <div class="col-12">
    <label class="form-label small-label">Title*</label>
    <input class="form-control" name="data[title]" required>
  </div>
  <div class="col-md-5">
    <label class="form-label small-label">Link Text</label>
    <input class="form-control" name="data[link_text]">
  </div>
  ${linkControl('data[link]', 'Link', 'col-md-7')}
  <div class="col-12">
    <label class="form-label small-label">Description</label>
    <textarea class="form-control" name="data[desc]" rows="3"></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label small-label">Hero Image</label>
    <input class="form-control" type="file" name="hero_image" accept=".webp,.jpg,.jpeg,.png" data-file-key="hero_image">
    <div class="mt-2"><img data-preview-key="hero_image" style="max-width:100%;height:auto;display:none"></div>
  </div>
</div>`);

                case 'hero3':
                    return html(`
<div class="row g-3">
  <div class="col-12">
    <label class="form-label small-label">Title*</label>
    <input class="form-control" name="data[title]" required>
  </div>
  <div class="col-12">
    <label class="form-label small-label">Description</label>
    <textarea class="form-control" name="data[description]" rows="3" placeholder="Short supporting copy"></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label small-label">Badge Icon</label>
    <select class="form-select" data-icon-select name="data[icon_bi]">
      <option value="">Hide badge</option>
    </select>
    <small class="text-muted">Search Bootstrap icons (bi-*)</small>
  </div>
  <div class="col-md-6">
    <label class="form-label small-label">Banner Image</label>
    <input class="form-control" type="file" name="banner_image" accept=".webp,.jpg,.jpeg,.png" data-file-key="banner_image">
    <div class="mt-2"><img data-preview-key="banner_image" style="max-width:100%;height:auto;display:none"></div>
  </div>
</div>`);

                case 'hero4':
                    return html(`
<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label small-label">Kicker</label>
    <input class="form-control" name="data[kicker]">
  </div>
  <div class="col-md-8">
    <label class="form-label small-label">Headline*</label>
    <input class="form-control" name="data[title]" required>
  </div>
  <div class="col-12">
    <label class="form-label small-label">Subtitle</label>
    <input class="form-control" name="data[subtitle]">
  </div>
  <div class="col-12">
    <label class="form-label small-label">Description</label>
    <textarea class="form-control" name="data[description]" rows="3"></textarea>
  </div>
  <div class="col-md-6">
    <label class="form-label small-label">Primary Button Text</label>
    <input class="form-control" name="data[primary_button_text]">
  </div>
  ${linkControl('data[primary_button_link]', 'Primary Button Link', 'col-md-6')}
  <div class="col-md-6">
    <label class="form-label small-label">Secondary Button Text</label>
    <input class="form-control" name="data[secondary_button_text]">
  </div>
  ${linkControl('data[secondary_button_link]', 'Secondary Button Link', 'col-md-6')}
  <div class="col-md-6">
    <label class="form-label small-label">Hero Image</label>
    <input class="form-control" type="file" name="hero_image" accept=".webp,.jpg,.jpeg,.png" data-file-key="hero_image">
    <div class="mt-2"><img data-preview-key="hero_image" style="max-width:100%;height:auto;display:none"></div>
  </div>
</div>`);

                case 'overview':
                case 'program_overview':
                    return buildOverviewFields();

                case 'about':
                    return html(`
<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label small-label">Kicker</label>
    <input class="form-control" name="data[kicker]">
  </div>
  <div class="col-md-6">
    <label class="form-label small-label">Title*</label>
    <input class="form-control" name="data[title]" required>
  </div>
  <div class="col-md-2">
    <label class="form-label small-label">Text Color</label>
    <input class="form-control form-control-color w-100" type="color" name="data[text_color]" value="#ffffff" title="Heading &amp; body text color">
  </div>
  <div class="col-12">
    <label class="form-label small-label">Subtitle</label>
    <input class="form-control" name="data[subtitle]">
  </div>
  <div class="col-12">
    <label class="form-label small-label">Text</label>
    <textarea class="form-control" name="data[text]" rows="3"></textarea>
  </div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Banner Image</span></div></div>
  <div class="col-md-6">
    <label class="form-label small-label">Banner (left visual)</label>
    <input class="form-control" type="file" name="banner_left" accept=".webp,.jpg,.jpeg,.png" data-file-key="banner_left">
    <div class="mt-2"><img data-preview-key="banner_left" style="max-width:100%;height:auto;display:none"></div>
  </div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">List</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Items</label>
    <button type="button" class="btn btn-outline-primary btn-sm" data-repeater-add="list">Add Item</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="list">
      <template data-repeater-template>
        <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-repeater-item draggable="true">
          <span class="drag-handle">☰</span>
          <input class="form-control" name="data[list][__INDEX__]">
          <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>×</button>
        </div>
      </template>
    </div>
  </div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">CTA</span></div></div>
  <div class="col-md-5">
    <label class="form-label small-label">CTA Text</label>
    <input class="form-control" name="data[cta][link_text]">
  </div>
  ${linkControl('data[cta][link]', 'CTA Link', 'col-md-7')}
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Cards</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Cards</label>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-repeater-add="cards">Add Card</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="cards">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6">
                <label class="form-label small-label">Title*</label>
                <input class="form-control" name="data[cards][__INDEX__][title]" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small-label">Text</label>
                <input class="form-control" name="data[cards][__INDEX__][text]">
              </div>
              <div class="col-12">
                <label class="form-label small-label">Image</label>
                <input class="form-control" type="file" name="cards[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png" data-file-key="cards___INDEX___image">
                <div class="mt-2"><img data-preview-key="cards___INDEX___image" style="max-width:100%;height:auto;display:none"></div>
              </div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Tiles</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Tiles</label>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-repeater-add="tiles">Add Tile</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="tiles">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-4">
                <label class="form-label small-label">Type</label>
                <select class="form-select" name="data[tiles][__INDEX__][type]" data-tile-type="__INDEX__">
                  <option value="counter">Counter</option>
                  <option value="image">Image</option>
                  <option value="customers">Customers</option>
                </select>
              </div>
              <div class="col-12" data-tile-group="counter-__INDEX__" style="display:none">
                <div class="row g-2">
                  <div class="col-md-3"><label class="form-label small-label">Label</label><input class="form-control" name="data[tiles][__INDEX__][label]"></div>
                  <div class="col-md-3"><label class="form-label small-label">Value</label><input class="form-control" name="data[tiles][__INDEX__][value]"></div>
                  <div class="col-md-3"><label class="form-label small-label">Suffix</label><input class="form-control" name="data[tiles][__INDEX__][suffix]"></div>
                  <div class="col-md-3"><label class="form-label small-label">Note</label><input class="form-control" name="data[tiles][__INDEX__][note]"></div>
                </div>
              </div>
              <div class="col-12" data-tile-group="image-__INDEX__" style="display:none">
                <label class="form-label small-label">BG Image</label>
                <input class="form-control" type="file" name="tiles[__INDEX__][bg]" accept=".webp,.jpg,.jpeg,.png" data-file-key="tiles___INDEX___bg">
                <div class="mt-2"><img data-preview-key="tiles___INDEX___bg" style="max-width:100%;height:auto;display:none"></div>
              </div>
              <div class="col-12" data-tile-group="customers-__INDEX__" style="display:none">
                <div class="row g-2">
                  <div class="col-12">
                    <label class="form-label small-label">BG Image</label>
                    <input class="form-control" type="file" name="tiles[__INDEX__][bg]" accept=".webp,.jpg,.jpeg,.png" data-file-key="tiles___INDEX___bg">
                    <div class="mt-2"><img data-preview-key="tiles___INDEX___bg" style="max-width:100%;height:auto;display:none"></div>
                  </div>
                  <div class="col-12"><label class="form-label small-label">Text</label><input class="form-control" name="data[tiles][__INDEX__][text]"></div>
                  <div class="col-md-5"><label class="form-label small-label">Link Text</label><input class="form-control" name="data[tiles][__INDEX__][link_text]"></div>
                  ${linkControl('data[tiles][__INDEX__][link]', 'Link', 'col-md-7')}
                </div>
              </div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'about2':
                    return html(`
<div class="row g-3">
  <div class="col-md-4"><label class="form-label small-label">Kicker</label><input class="form-control" name="data[kicker]"></div>
  <div class="col-md-6"><label class="form-label small-label">Title*</label><input class="form-control" name="data[title]" required></div>
  <div class="col-md-2">
    <label class="form-label small-label">Text Color</label>
    <input class="form-control form-control-color w-100" type="color" name="data[text_color]" value="#ffffff" title="Heading &amp; body text color">
  </div>
  <div class="col-12"><label class="form-label small-label">Text</label><textarea class="form-control" name="data[text]" rows="3"></textarea></div>
  <div class="col-md-5"><label class="form-label small-label">Link Text</label><input class="form-control" name="data[link_text]"></div>
  ${linkControl('data[link]', 'Link', 'col-md-7')}
  <div class="col-md-6">
    <label class="form-label small-label">Image</label>
    <input class="form-control" type="file" name="about_image" accept=".webp,.jpg,.jpeg,.png" data-file-key="about_image">
    <div class="mt-2"><img data-preview-key="about_image" style="max-width:100%;height:auto;display:none"></div>
  </div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Columns</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Columns</label>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-repeater-add="columns">Add Column</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="columns" data-max="2">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6"><label class="form-label small-label">Head</label><input class="form-control" name="data[columns][__INDEX__][head]"></div>
              <div class="col-md-6"><label class="form-label small-label">Subhead</label><input class="form-control" name="data[columns][__INDEX__][subhead]"></div>
              <div class="col-12"><label class="form-label small-label">Description</label><textarea class="form-control" rows="2" name="data[columns][__INDEX__][description]"></textarea></div>
              <div class="col-12">
                <div class="d-flex justify-content-between mb-2">
                  <label class="form-label small-label mb-0">List</label>
                  <button class="btn btn-outline-primary btn-sm" type="button" data-repeater-add="columns___INDEX___list">Add Item</button>
                </div>
                <div class="repeater" data-repeater data-name="columns___INDEX___list">
                  <template data-repeater-template>
                    <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-repeater-item draggable="true">
                      <span class="drag-handle">☰</span>
                      <input class="form-control" name="data[columns][__INDEX__][list][__SUBINDEX__]">
                      <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>×</button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'sections':
                    return html(`
<div class="row g-3">
  <div class="col-md-4"><label class="form-label small-label">Kicker</label><input class="form-control" name="data[kicker]"></div>
  <div class="col-md-8"><label class="form-label small-label">Title*</label><input class="form-control" name="data[title]" required></div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Items</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Services</label>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-repeater-add="items">Add Service</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6"><label class="form-label small-label">Title*</label><input class="form-control" name="data[items][__INDEX__][title]" required></div>
              <div class="col-md-6"><label class="form-label small-label">Subtitle</label><input class="form-control" name="data[items][__INDEX__][subtitle]"></div>
              <div class="col-md-6"><label class="form-label small-label">Link Text</label><input class="form-control" name="data[items][__INDEX__][link_text]"></div>
              <div class="col-12"><label class="form-label small-label">Text</label><textarea class="form-control" name="data[items][__INDEX__][text]" rows="2"></textarea></div>
              ${linkControl('data[items][__INDEX__][link]', 'Link', 'col-md-6')}
              <div class="col-md-6">
                <label class="form-label small-label">Image</label>
                <input class="form-control" type="file" name="items[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png" data-file-key="items___INDEX___image">
                <div class="mt-2"><img data-preview-key="items___INDEX___image" style="max-width:100%;height:auto;display:none"></div>
              </div>
              <div class="col-12">
                <div class="d-flex justify-content-between mb-2 mt-2">
                  <label class="form-label small-label mb-0">List</label>
                  <button class="btn btn-outline-primary btn-sm" type="button" data-repeater-add="items___INDEX___list">Add Item</button>
                </div>
                <div class="repeater" data-repeater data-name="items___INDEX___list">
                  <template data-repeater-template>
                    <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-repeater-item draggable="true">
                      <span class="drag-handle">☰</span>
                      <input class="form-control" name="data[items][__INDEX__][list][__SUBINDEX__]">
                      <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>×</button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'sections2':
                    return html(`
<div class="row g-3">
  <div class="col-md-4"><label class="form-label small-label">Kicker</label><input class="form-control" name="data[kicker]"></div>
  <div class="col-md-8"><label class="form-label small-label">Title*</label><input class="form-control" name="data[title]" required></div>
  <div class="col-12"><label class="form-label small-label">Description</label><textarea class="form-control" rows="2" name="data[desc]"></textarea></div>
  <div class="col-md-6"><label class="form-label small-label">Link Text</label><input class="form-control" name="data[link_text]"></div>
  ${linkControl('data[link]', 'Link', 'col-md-6')}
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Items</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Items</label>
    <button class="btn btn-dark btn-sm" type="button" data-repeater-add="items">Add Item</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6"><label class="form-label small-label">Title*</label><input class="form-control" name="data[items][__INDEX__][title]" required></div>
              <div class="col-md-6">
                <label class="form-label small-label">Image</label>
                <input class="form-control" type="file" name="items[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png" data-file-key="items___INDEX___image">
                <div class="mt-2"><img data-preview-key="items___INDEX___image" style="max-width:100%;height:auto;display:none"></div>
              </div>
              <div class="col-12"><label class="form-label small-label">Subtitle</label><input class="form-control" name="data[items][__INDEX__][subtitle]"></div>
              <div class="col-12"><label class="form-label small-label">Description</label><textarea class="form-control" rows="2" name="data[items][__INDEX__][description]"></textarea></div>
              <div class="col-12">
                <div class="d-flex justify-content-between mb-2">
                  <label class="form-label small-label mb-0">List</label>
                  <button class="btn btn-outline-primary btn-sm" type="button" data-repeater-add="items___INDEX___list">Add Item</button>
                </div>
                <div class="repeater" data-repeater data-name="items___INDEX___list">
                  <template data-repeater-template>
                    <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-repeater-item draggable="true">
                      <span class="drag-handle">☰</span>
                      <input class="form-control" name="data[items][__INDEX__][list][__SUBINDEX__]">
                      <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>×</button>
                    </div>
                  </template>
                </div>
              </div>
              <div class="col-md-5"><label class="form-label small-label">Link Text</label><input class="form-control" name="data[items][__INDEX__][link_text]"></div>
              ${linkControl('data[items][__INDEX__][link]', 'Link', 'col-md-7')}
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);
                case 'overview2':
                    return html(`
<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label small-label">Kicker</label>
    <input class="form-control" name="data[kicker]" placeholder="OUR PROGRAM">
  </div>
  <div class="col-md-8">
    <label class="form-label small-label">Title*</label>
    <input class="form-control" name="data[title]" placeholder="Program Includes:" required>
  </div>
  <div class="col-12">
    <label class="form-label small-label">Subtitle</label>
    <input class="form-control" name="data[subtitle]" placeholder="What you get">
  </div>
  <div class="col-12">
    <label class="form-label small-label">Description</label>
    <textarea class="form-control" name="data[desc]" rows="2" placeholder="Short overview paragraph..."></textarea>
  </div>
  
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Top-level List (Optional)</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Bullet Points</label>
    <button type="button" class="btn btn-outline-primary btn-sm" data-repeater-add="list">Add Item</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="list">
      <template data-repeater-template>
        <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-repeater-item draggable="true">
          <span class="drag-handle">☰</span>
          <input class="form-control" name="data[list][__INDEX__]" placeholder="Live classes, Hands-on labs, etc.">
          <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove">×</button>
        </div>
      </template>
    </div>
  </div>

  <div class="col-md-5">
    <label class="form-label small-label">Default Link Text</label>
    <input class="form-control" name="data[link_text]" placeholder="Learn More">
  </div>
  ${linkControl('data[link]', 'Default Link URL', 'col-md-7', '#')}

  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Program Items</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Items</label>
    <button class="btn btn-dark btn-sm" type="button" data-repeater-add="items">Add Item</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6">
                <label class="form-label small-label">Title*</label>
                <input class="form-control" name="data[items][__INDEX__][title]" placeholder="Foundations (5 Weeks)" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small-label">Image</label>
                <input class="form-control" type="file" name="items[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png" data-file-key="items___INDEX___image">
                <div class="mt-2"><img data-preview-key="items___INDEX___image" style="max-width:100%;height:auto;display:none"></div>
              </div>
              <div class="col-12">
                <label class="form-label small-label">Description</label>
                <textarea class="form-control" rows="2" name="data[items][__INDEX__][description]" placeholder="15 live classes, mentor support..."></textarea>
              </div>
              
              <div class="col-12">
                <div class="d-flex justify-content-between mb-2 mt-2">
                  <label class="form-label small-label mb-0">Item Features List</label>
                  <button class="btn btn-outline-primary btn-sm" type="button" data-repeater-add="items___INDEX___list">Add Feature</button>
                </div>
                <div class="repeater" data-repeater data-name="items___INDEX___list">
                  <template data-repeater-template>
                    <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-repeater-item draggable="true">
                      <span class="drag-handle">☰</span>
                      <input class="form-control" name="data[items][__INDEX__][list][__SUBINDEX__]" placeholder="Live cohort, Projects, etc.">
                      <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove">×</button>
                    </div>
                  </template>
                </div>
              </div>

              ${linkControl('data[items][__INDEX__][link]', 'Item Link URL (overrides default)', 'col-md-12', '/programs/foundations')}
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove">Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);
                case 'logo_slider':
                    return html(`
<div class="row g-3">
  <div class="col-md-4"><label class="form-label small-label">Kicker</label><input class="form-control" name="data[kicker]"></div>
  <div class="col-md-8"><label class="form-label small-label">Title*</label><input class="form-control" name="data[title]" required></div>
  <div class="col-md-5"><label class="form-label small-label">Link Text</label><input class="form-control" name="data[link_text]"></div>
  ${linkControl('data[link]', 'Link', 'col-md-7')}
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Logos</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Logos</label>
    <button class="btn btn-dark btn-sm" type="button" data-repeater-add="logos">Add Logo</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="logos">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6">
                <label class="form-label small-label">Image</label>
                <input class="form-control" type="file" name="logos[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png" data-file-key="logos___INDEX___image">
                <div class="mt-2"><img data-preview-key="logos___INDEX___image" style="max-width:220px;height:auto;display:none"></div>
              </div>
              <div class="col-md-6"><label class="form-label small-label">Alt</label><input class="form-control" name="data[logos][__INDEX__][alt]"></div>
              ${linkControl('data[logos][__INDEX__][href]', 'Link', 'col-12')}
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'gallary':
                    return html(`
<div class="row g-3">
  <div class="col-md-4"><label class="form-label small-label">Kicker</label><input class="form-control" name="data[kicker]"></div>
  <div class="col-md-8"><label class="form-label small-label">Title*</label><input class="form-control" name="data[section_title]" required></div>
  <div class="col-md-5"><label class="form-label small-label">Link Text</label><input class="form-control" name="data[link_text]"></div>
  ${linkControl('data[link]', 'Link', 'col-md-7')}
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Items</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Projects</label>
    <button class="btn btn-outline-secondary btn-sm" data-repeater-add="items" type="button">Add</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6"><label class="form-label small-label">Title*</label><input class="form-control" name="data[items][__INDEX__][title]" required></div>
              <div class="col-md-6">
                <label class="form-label small-label">Image</label>
                <input class="form-control" type="file" name="items[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png" data-file-key="items___INDEX___image">
                <div class="mt-2"><img data-preview-key="items___INDEX___image" style="max-width:100%;height:auto;display:none"></div>
              </div>
              <div class="col-md-5"><label class="form-label small-label">Link Text</label><input class="form-control" name="data[items][__INDEX__][link_text]"></div>
              ${linkControl('data[items][__INDEX__][link]', 'Link', 'col-md-7')}
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'testimonial':
                    return html(`
<div class="row g-3">
  <div class="col-md-4"><label class="form-label small-label">Kicker</label><input class="form-control" name="data[kicker]"></div>
  <div class="col-md-8"><label class="form-label small-label">Title*</label><input class="form-control" name="data[title]" required></div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">Testimonials</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Items</label>
    <button class="btn btn-outline-secondary btn-sm" data-repeater-add="items" type="button">Add</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-4"><label class="form-label small-label">Name*</label><input class="form-control" name="data[items][__INDEX__][name]" required></div>
              <div class="col-md-4"><label class="form-label small-label">Designation</label><input class="form-control" name="data[items][__INDEX__][designation]"></div>
              <div class="col-md-4">
                <label class="form-label small-label">Photo</label>
                <input class="form-control" type="file" name="items[__INDEX__][photo]" accept=".webp,.jpg,.jpeg,.png" data-file-key="items___INDEX___photo">
                <div class="mt-2"><img data-preview-key="items___INDEX___photo" style="max-width:100%;height:60px;display:none;border-radius:50%"></div>
              </div>
              <div class="col-md-9"><label class="form-label small-label">Text*</label><textarea class="form-control" rows="3" name="data[items][__INDEX__][text]" required></textarea></div>
              <div class="col-md-3"><label class="form-label small-label">Rating %</label><input class="form-control" type="number" min="0" max="100" name="data[items][__INDEX__][rating_fill]" value="100"></div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'faq':
                    return html(`
<div class="row g-3">
  <div class="col-md-6"><label class="form-label small-label">Title</label><input class="form-control" name="data[title]"></div>
  <div class="col-md-6"><label class="form-label small-label">Subtitle</label><input class="form-control" name="data[subtitle]"></div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">FAQs</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Items</label>
    <button class="btn btn-dark btn-sm" type="button" data-repeater-add="items">Add FAQ</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="items">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-12"><label class="form-label small-label">Question*</label><input class="form-control" name="data[items][__INDEX__][q]" required></div>
              <div class="col-12"><label class="form-label small-label">Answer*</label><textarea class="form-control" rows="3" name="data[items][__INDEX__][a]" required></textarea></div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'closing_cta':
                    return html(`
<div class="row g-3">
  <div class="col-12"><label class="form-label small-label">Title*</label><input class="form-control" name="data[title]" required></div>
  <div class="col-12"><label class="form-label small-label">Subtitle</label><textarea class="form-control" rows="2" name="data[subtitle]"></textarea></div>
  <div class="col-12"><div class="section-divider"><span class="section-divider-label">CTAs</span></div></div>
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Buttons</label>
    <button class="btn btn-dark btn-sm" type="button" data-repeater-add="ctas">Add CTA</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="ctas" data-max="3">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-5"><label class="form-label small-label">Text*</label><input class="form-control" name="data[ctas][__INDEX__][link_text]" required></div>
              ${linkControl('data[ctas][__INDEX__][link]', 'Link', 'col-md-7')}
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'marquees':
                    return html(`
<div class="row g-3">
  <div class="col-12 d-flex justify-content-between">
    <label class="form-label small-label mb-0">Slides</label>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-repeater-add="slides">Add Slide</button>
  </div>
  <div class="col-12">
    <div class="repeater" data-repeater data-name="slides">
      <template data-repeater-template>
        <div class="repeater-item border rounded-12 p-3 mb-3" data-repeater-item draggable="true">
          <div class="d-flex gap-3 align-items-start">
            <span class="drag-handle">☰</span>
            <div class="row g-2 flex-grow-1">
              <div class="col-md-6"><label class="form-label small-label">Title*</label><input class="form-control" name="data[slides][__INDEX__][title]" required></div>
              <div class="col-md-6">
                <label class="form-label small-label">Image</label>
                <input class="form-control" type="file" name="slides[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png" data-file-key="slides___INDEX___image">
                <div class="mt-2"><img data-preview-key="slides___INDEX___image" style="max-width:100%;height:auto;display:none"></div>
              </div>
            </div>
            <button class="btn btn-outline-danger btn-sm" type="button" data-repeater-remove>Remove</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>`);

                case 'form_dark':
                case 'form_light':
                    return renderFormBuilderFields();


                default:
                    return html(`<div class="alert alert-info">Select a block type to see its fields.</div>`);
            }
        }


        /* ====== RICH TEXT (page sections) ====== */
        function initRichText(mount, initial) {
            const area = mount.querySelector('[data-rt-area]');
            const input = mount.querySelector('[data-rt-input]');
            const linkbox = mount.querySelector('.rt-linkbox');
            if (!area || !input) return;
            area.innerHTML = initial;
            const sync = () => { input.value = area.innerHTML; };
            sync();
            area.addEventListener('input', sync);
            area.addEventListener('focus', () => { try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) {} });
            area.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text/plain');
                const esc = (t) => t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                document.execCommand('insertHTML', false, text.split(/\r?\n\s*\r?\n/).map(p => '<p>' + esc(p).replace(/\r?\n/g, '<br>') + '</p>').join(''));
                sync();
            });
            let saved = null;
            mount.querySelector('.rt-bar').addEventListener('mousedown', (e) => { if (!e.target.closest('input')) e.preventDefault(); });
            mount.querySelector('.rt-bar').addEventListener('click', (e) => {
                const b = e.target.closest('button');
                if (!b) return;
                if (b.dataset.cmd) { area.focus(); document.execCommand(b.dataset.cmd); sync(); }
                else if (b.dataset.block) { area.focus(); document.execCommand('formatBlock', false, b.dataset.block); sync(); }
                else if (b.hasAttribute('data-link')) {
                    const sel = window.getSelection();
                    if (!sel.rangeCount || sel.isCollapsed || !area.contains(sel.anchorNode)) { iziToast.info({ title: 'Select some text first', message: '', position: 'topRight', timeout: 2500 }); return; }
                    saved = sel.getRangeAt(0).cloneRange();
                    linkbox.hidden = false; linkbox.querySelector('input').value = ''; linkbox.querySelector('input').focus();
                } else if (b.hasAttribute('data-link-apply') || b.hasAttribute('data-link-remove')) {
                    const url = linkbox.querySelector('input').value.trim();
                    area.focus(); const s2 = window.getSelection(); s2.removeAllRanges(); if (saved) s2.addRange(saved);
                    if (b.hasAttribute('data-link-remove')) document.execCommand('unlink');
                    else if (url) document.execCommand('createLink', false, /^[a-z]+:|^[\/#]/i.test(url) ? url : 'https://' + url);
                    linkbox.hidden = true; sync();
                }
            });
            linkbox.querySelector('input').addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); linkbox.querySelector('[data-link-apply]').click(); } });
        }

        /* ====== TYPE-SPECIFIC BINDINGS ====== */
        function attachTypeBindings(type, mount, mode, existing) {

            if (type === 'rich_text') {
                initRichText(mount, existing.body || '');
            }
            if (type === 'hero') {
                const title = mount.querySelector('input[name="data[title]"]');
                const slug = mount.querySelector('input[name="data[slug]"][data-hero-slug]');
                if (slug && title) {
                    slug.value = existing.slug || slugify(title.value || '');
                    title.addEventListener('input', () => {
                        slug.value = slugify(title.value);
                    });
                }
            }

            if (type === 'about') {
                // Tile type toggle
                mount.addEventListener('change', (e) => {
                    const sel = e.target.closest('[data-tile-type]');
                    if (sel) {
                        const idx = sel.getAttribute('data-tile-type');
                        toggleTileGroups(mount, idx, sel.value);
                    }
                });

                // Initialize existing tiles
                if (existing.tiles && Array.isArray(existing.tiles)) {
                    requestAnimationFrame(() => requestAnimationFrame(() => {
                        existing.tiles.forEach((tile, i) => {
                            const sel = mount.querySelector(`[data-tile-type="${i}"]`);
                            if (sel) {
                                sel.value = tile.type || 'counter';
                                toggleTileGroups(mount, i, sel.value);
                            }
                        });
                    }));
                }
            }


        }

        function toggleTileGroups(scope, idx, typeVal) {
            ['counter', 'image', 'customers'].forEach((g) => {
                const el = scope.querySelector(`[data-tile-group="${g}-${idx}"]`);
                if (el) {
                    el.style.display = g === typeVal ? '' : 'none';
                }
            });
        }

        /* ====== HYDRATION ====== */

        function isSimpleObject(obj) {

            return obj && typeof obj === 'object' && !Array.isArray(obj);

        }



        /* ====== REPEATER SYSTEM ====== */

        /* ====== ENHANCED REPEATER SYSTEM WITH PROPER LIST HANDLING ====== */



        // Global index counter for unique IDs

        let globalRepeaterCounter = 0;



        function initRepeater(root = document) {

            root.querySelectorAll('.repeater[data-name]').forEach((rep) => {

                if (rep.dataset.repeaterReady) return;

                rep.dataset.repeaterReady = '1';



                const name = rep.getAttribute('data-name');

                const tpl = rep.querySelector('template[data-repeater-template]');

                if (!tpl) return;



                // Setup add buttons

                root.querySelectorAll(`[data-repeater-add="${name}"]`).forEach((btn) => {

                    if (btn.dataset.listenerAdded) return;

                    btn.dataset.listenerAdded = '1';



                    btn.addEventListener('click', (e) => {

                        e.preventDefault();

                        e.stopPropagation();



                        const max = parseInt(rep.dataset.max || '0', 10);

                        const count = rep.querySelectorAll(':scope > [data-repeater-item]').length;



                        if (max && count >= max) {

                            iziToast.warning({ title: 'Limit Reached', message: `Maximum ${max} items allowed`, position: 'topRight', timeout: 3000 });

                            return;
                        }



                        addRepeaterItem(rep, tpl);

                    });

                });



                // Remove delegation

                rep.addEventListener('click', (e) => {

                    const btn = e.target.closest('[data-repeater-remove]');

                    if (!btn) return;



                    const item = btn.closest('[data-repeater-item]');

                    if (!item || item.parentElement !== rep) return;



                    e.preventDefault();

                    e.stopPropagation();

                    item.remove();

                    renumberRepeater(rep);



                    // Renumber parent if this is nested

                    const parentRepeater = rep.closest('[data-repeater-item]')?.closest(

                        '.repeater[data-name]');

                    if (parentRepeater) {

                        renumberRepeater(parentRepeater);

                    }

                });



                // Drag and drop

                setupDragDrop(rep);

            });

        }



        function addRepeaterItem(rep, tpl) {

            const item = tpl.content.firstElementChild.cloneNode(true);

            item.setAttribute('draggable', 'true');



            // Assign unique temporary ID for nested repeater tracking

            item.dataset.tempId = `temp_${globalRepeaterCounter++}`;



            rep.appendChild(item);



            // Renumber this repeater

            renumberRepeater(rep);



            // Initialize nested repeaters inside the new item

            initRepeater(item);
            initLinkPickers(item);
            initIconPickers(item);



            // If this repeater has a parent repeater, trigger full renumbering

            const parentRepeater = rep.closest('[data-repeater-item]')?.closest('.repeater[data-name]');

            if (parentRepeater) {

                setTimeout(() => renumberRepeater(parentRepeater), 50);

            }

        }



        function renumberRepeater(rep) {



            const items = [...rep.querySelectorAll(':scope > [data-repeater-item]')];







            items.forEach((item, i) => {



                // Update ALL field names in this item



                item.querySelectorAll('[name]').forEach((field) => {



                    let name = field.name;







                    // Handle __INDEX__ replacement



                    name = name.replace(/\[__INDEX__\]/g, `[${i}]`);







                    // Handle __SUBINDEX__ for nested items



                    if (name.includes('__SUBINDEX__')) {



                        // Don't replace __SUBINDEX__ at parent level - let nested renumber handle it



                        return;



                    }







                    field.name = name;



                });







                // Update data attributes



                item.querySelectorAll('[data-file-key], [data-preview-key], [data-tile-type], [data-tile-group]')



                    .forEach((el) => {



                        ['data-file-key', 'data-preview-key', 'data-tile-type', 'data-tile-group'].forEach((



                            attr) => {



                            if (el.hasAttribute(attr)) {



                                let val = el.getAttribute(attr);



                                val = val.replace(/___INDEX___/g, `_${i}_`);



                                val = val.replace(/__INDEX__/g, i);



                                el.setAttribute(attr, val);



                            }



                        });



                    });







                // Update nested repeater add button names



                item.querySelectorAll('[data-repeater-add]').forEach((btn) => {



                    const name = btn.getAttribute('data-repeater-add');



                    const updated = name.replace(/___INDEX___/g, `_${i}_`);



                    btn.setAttribute('data-repeater-add', updated);



                });







                // Update nested repeater data-name attributes AND renumber them



                item.querySelectorAll('.repeater[data-name]').forEach((nestedRep) => {



                    const name = nestedRep.getAttribute('data-name');



                    const updated = name.replace(/___INDEX___/g, `_${i}_`);



                    nestedRep.setAttribute('data-name', updated);







                    // Renumber nested items



                    const nestedItems = [...nestedRep.querySelectorAll(':scope > [data-repeater-item]')];



                    nestedItems.forEach((nestedItem, j) => {



                        nestedItem.querySelectorAll('[name]').forEach((field) => {



                            let name = field.name;







                            // Replace subindex first



                            name = name.replace(/\[__SUBINDEX__\]/g, `[${j}]`);







                            // Then replace parent index



                            name = name.replace(/\[__INDEX__\]/g, `[${i}]`);







                            field.name = name;



                        });



                    });



                });



            });



        }







        function setupDragDrop(rep) {



            let dragging = null;







            rep.addEventListener('dragstart', (e) => {



                const item = e.target.closest('[data-repeater-item]');



                if (!item || item.parentElement !== rep) return;







                dragging = item;



                item.classList.add('dragging');



                e.dataTransfer.effectAllowed = 'move';



            });







            rep.addEventListener('dragend', (e) => {



                const item = e.target.closest('[data-repeater-item]');



                if (!item) return;







                item.classList.remove('dragging');



                dragging = null;



                renumberRepeater(rep);



            });







            rep.addEventListener('dragover', (e) => {



                e.preventDefault();



                if (!dragging) return;







                const items = [...rep.querySelectorAll(':scope > [data-repeater-item]:not(.dragging)')];



                const afterElement = items.find((item) => {



                    const box = item.getBoundingClientRect();



                    return e.clientY < box.top + box.height / 2;



                });







                if (afterElement) {



                    rep.insertBefore(dragging, afterElement);



                } else {



                    rep.appendChild(dragging);



                }



            });



        }







        /* ====== IMPROVED HYDRATION WITH BETTER LIST HANDLING ====== */



        function hydrateFields(scope, data) {

            if (!data || typeof data !== 'object') return;



            console.log('📄 Starting hydration with data:', data);



            // Hydrate scalars

            for (const [key, val] of Object.entries(data)) {

                if (val === null || val === undefined) continue;

                if (Array.isArray(val) || (typeof val === 'object' && !isSimpleObject(val))) continue;

                setVal(`data[${key}]`, val, scope);

            }



            // Hydrate nested objects (non-arrays)

            for (const [key, val] of Object.entries(data)) {

                if (!val || typeof val !== 'object' || Array.isArray(val)) continue;

                for (const [k2, v2] of Object.entries(val)) {

                    if (v2 === null || v2 === undefined) continue;

                    if (Array.isArray(v2) || (typeof v2 === 'object')) continue;

                    setVal(`data[${key}][${k2}]`, v2, scope);

                }

            }



            // Hydrate arrays

            for (const [arrKey, arrVal] of Object.entries(data)) {

                if (!Array.isArray(arrVal)) continue;



                console.log(`📋 Processing array: ${arrKey}`, arrVal);



                const addBtn = scope.querySelector(`[data-repeater-add="${arrKey}"]`);

                const repeater = scope.querySelector(`.repeater[data-name="${arrKey}"]`);



                if (!addBtn || !repeater) {

                    console.warn(`⚠ Repeater not found for: ${arrKey}`);

                    continue;

                }



                // Add items

                console.log(`➕ Adding ${arrVal.length} items to ${arrKey}`);

                for (let i = 0; i < arrVal.length; i++) {

                    addBtn.click();

                }



                // Hydrate after two paint frames (replaces fragile setTimeout)

                requestAnimationFrame(() => requestAnimationFrame(() => {

                    console.log(`💾 Hydrating ${arrKey} items...`);



                    arrVal.forEach((row, i) => {

                        if (row && typeof row === 'object' && !Array.isArray(row)) {

                            Object.entries(row).forEach(([rk, rv]) => {

                                if (rv === null || rv === undefined) return;



                                if (Array.isArray(rv)) {

                                    console.log(`🔗 Found nested array: ${arrKey}[${i}].${rk}`, rv);

                                    hydrateNestedArray(scope, arrKey, i, rk, rv);

                                } else if (typeof rv === 'object') {

                                    return;

                                } else {

                                    setVal(`data[${arrKey}][${i}][${rk}]`, rv, scope);

                                }

                            });

                        } else {

                            setVal(`data[${arrKey}][${i}]`, row, scope);

                        }

                    });



                    console.log(`✅ Finished hydrating ${arrKey}`);

                }));

            }

        }



        function hydrateNestedArray(scope, parentKey, parentIdx, childKey, childArray) {

            requestAnimationFrame(() => requestAnimationFrame(() => {

                const nestedName = `${parentKey}_${parentIdx}_${childKey}`;

                let addBtn = scope.querySelector(`[data-repeater-add="${nestedName}"]`);



                if (!addBtn) {

                    console.warn(`⚠ Add button not found for: ${nestedName}`);

                    return;

                }



                console.log(`✅ Found nested repeater: ${nestedName}, adding ${childArray.length} items`);



                // Add all items first

                for (let j = 0; j < childArray.length; j++) {

                    addBtn.click();

                }



                // Force renumber of parent

                const parentRepeater = scope.querySelector(`.repeater[data-name="${parentKey}"]`);

                if (parentRepeater) {

                    renumberRepeater(parentRepeater);

                }



                // Hydrate after renumbering (two paint frames, no fixed-ms fragility)

                requestAnimationFrame(() => requestAnimationFrame(() => {

                    childArray.forEach((item, j) => {

                        if (item && typeof item === 'object' && !Array.isArray(item)) {

                            Object.entries(item).forEach(([k, v]) => {

                                if (v !== null && v !== undefined && typeof v !==

                                    'object') {

                                    const fieldName =

                                        `data[${parentKey}][${parentIdx}][${childKey}][${j}][${k}]`;

                                    setVal(fieldName, v, scope);

                                    console.log(`  💾 Set: ${fieldName} = ${v}`);

                                }

                            });

                        } else {

                            const fieldName = `data[${parentKey}][${parentIdx}][${childKey}][${j}]`;

                            setVal(fieldName, item, scope);

                            console.log(`  💾 Set: ${fieldName} = ${item}`);

                        }

                    });



                    console.log(

                        `✅ Hydrated ${childArray.length} items in ${parentKey}[${parentIdx}].${childKey}`

                    );

                }));

            }));

        }



        /* ====== ENHANCED FINALIZATION WITH PROPER EMPTY CHECKING ====== */



        function pruneEmptyRepeaterItems(root = document) {

            const allRepeaters = root.querySelectorAll('.repeater[data-name]');



            allRepeaters.forEach((rep) => {

                const items = [...rep.querySelectorAll(':scope > [data-repeater-item]')];



                items.forEach((item) => {

                    // Check ALL text/textarea inputs (not file inputs)

                    const textInputs = item.querySelectorAll(

                        'input:not([type="file"]):not([type="hidden"]), textarea, select');



                    if (textInputs.length === 0) return;



                    // Check if ALL text inputs are empty

                    const allEmpty = [...textInputs].every(field => {

                        const val = field.value || '';

                        return val.trim() === '';

                    });



                    if (allEmpty) {

                        console.log('🗑️ Removing empty item');

                        item.remove();

                    }

                });

            });



            // Renumber after pruning

            allRepeaters.forEach(rep => renumberRepeater(rep));

        }



        function pruneEmptyListInputs(root = document) {

            // Find all simple list items (single input repeater items)

            root.querySelectorAll('.repeater[data-name*="list"]').forEach((listRepeater) => {

                const items = [...listRepeater.querySelectorAll(':scope > [data-repeater-item]')];



                items.forEach((item) => {

                    const inputs = item.querySelectorAll(

                        'input:not([type="file"]):not([type="hidden"]), textarea');



                    // If this item only has one input and it's empty, remove it

                    if (inputs.length === 1) {

                        const val = (inputs[0].value || '').trim();

                        if (val === '') {

                            console.log('🗑️ Removing empty list item');

                            item.remove();

                        }

                    }

                });

            });

        }



        function finalizeRepeaters(root = document) {

            // Get all repeaters in proper order (parents first)

            const repeaters = [...root.querySelectorAll('.repeater[data-name]')];



            // Sort by nesting depth (fewer ancestors first)

            repeaters.sort((a, b) => {

                const depthA = a.closest('[data-repeater-item]') ? 1 : 0;

                const depthB = b.closest('[data-repeater-item]') ? 1 : 0;

                return depthA - depthB;

            });



            // Renumber each repeater

            repeaters.forEach(rep => renumberRepeater(rep));

        }



        function stripLeftoverPlaceholders(root = document) {

            root.querySelectorAll('[name]').forEach((el) => {

                // Only replace if it's still a placeholder (shouldn't happen after proper renumbering)

                if (el.name.includes('__INDEX__') || el.name.includes('__SUBINDEX__')) {

                    console.warn('⚠ Found leftover placeholder:', el.name);

                    el.name = el.name.replace(/\[__INDEX__\]/g, '[0]');

                    el.name = el.name.replace(/\[__SUBINDEX__\]/g, '[0]');

                }

            });

        }










        function stripLeftoverPlaceholders(root = document) {

            root.querySelectorAll('[name]').forEach((el) => {

                el.name = el.name.replace(/\[__INDEX__\]/g, '[0]');

                el.name = el.name.replace(/\[__SUBINDEX__\]/g, '[0]');

            });

        }

        /* ====== BLOCK LIST (DRAG REORDER) ====== */
        function encodeBlockPayload(block) {
            return btoa(unescape(encodeURIComponent(JSON.stringify(block))));
        }

        function decodeBlockPayload(value) {
            return JSON.parse(decodeURIComponent(escape(atob(value || 'e30='))));
        }

        const blockList = document.getElementById('blockList');
        if (blockList) {
            // Edit button
            blockList.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action="edit"]');
                if (!btn) return;

                try {
                    const payload = decodeBlockPayload(btn.getAttribute('data-payload'));
                    openModal('edit', payload);
                } catch (err) {
                    console.error('Error parsing block data:', err);
                    iziToast.error({ title: 'Load Error', message: 'Error loading block data.', position: 'topRight' });
                }
            });

            // Quick publish toggle
            blockList.addEventListener('click', (e) => {
                const toggleBtn = e.target.closest('[data-action="toggle-publish"]');
                if (!toggleBtn) return;
                const newState = toggleBtn.dataset.published !== '1';
                fetch(@json(route('pb.blocks.publish', ['block' => '__ID__'])).replace('__ID__', toggleBtn.dataset.id), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRF(),
                    },
                    body: JSON.stringify({ is_published: newState }),
                    credentials: 'same-origin',
                }).then(r => r.json()).then(json => {
                    if (!json.ok) return;
                    toggleBtn.dataset.published = newState ? '1' : '0';
                    toggleBtn.title = newState ? 'Unpublish' : 'Publish';
                    toggleBtn.querySelector('i').className = `bi ${newState ? 'bi-eye-slash' : 'bi-eye'}`;
                    const badge = toggleBtn.closest('.block-row')?.querySelector('[data-pub-badge]');
                    if (badge) {
                        badge.className = newState ? 'badge bg-success-subtle text-success' : 'badge bg-danger-subtle text-danger';
                        badge.textContent = newState ? 'Live' : 'Draft';
                    }
                    pbRefreshPreview();
                    iziToast.success({ title: newState ? 'Published' : 'Unpublished', message: '', position: 'topRight', timeout: 2500 });
                }).catch(() => {
                    iziToast.error({ title: 'Error', message: 'Could not update publish status.', position: 'topRight' });
                });
            });

            // Clone block
            blockList.addEventListener('click', (e) => {
                const cloneBtn = e.target.closest('[data-action="clone"]');
                if (!cloneBtn) return;
                cloneBtn.disabled = true;
                fetch(@json(route('pb.blocks.clone', ['block' => '__ID__'])).replace('__ID__', cloneBtn.dataset.id), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
                    credentials: 'same-origin',
                }).then(r => r.json()).then(json => {
                    cloneBtn.disabled = false;
                    if (json.ok) {
                        iziToast.success({ title: 'Duplicated', message: json.message, position: 'topRight', timeout: 3500 });
                        window.location.reload();
                    }
                }).catch(() => { cloneBtn.disabled = false; });
            });

            // Delete block via AJAX
            blockList.addEventListener('submit', async (e) => {
                const delForm = e.target.closest('form[data-action="delete"]');
                if (!delForm) return;
                e.preventDefault();
                const row = delForm.closest('.block-row');
                const blockName = row?.querySelector('.fw-semibold')?.textContent.trim() || 'This block';
                const yes = await window.feConfirm({
                    title: 'Delete this block?',
                    message: blockName + ' will be removed from the page. This cannot be undone.',
                    action: 'Delete block'
                });
                if (!yes) return;
                try {
                    const res = await fetch(delForm.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
                        body: new FormData(delForm),
                        credentials: 'same-origin',
                    });
                    if (res.ok) {
                        row?.remove();
                        iziToast.success({ title: 'Deleted', message: 'Block removed.', position: 'topRight', timeout: 3000 });
                        pbRefreshPreview();
                    } else {
                        iziToast.error({ title: 'Error', message: 'Could not delete block.', position: 'topRight' });
                    }
                } catch {
                    iziToast.error({ title: 'Network Error', message: 'Delete failed. Please try again.', position: 'topRight' });
                }
            });

            // Drag reorder
            let dragging = null;

            blockList.addEventListener('dragstart', (e) => {
                const li = e.target.closest('.block-row[draggable="true"]');
                if (!li) return;

                dragging = li;
                li.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            blockList.addEventListener('dragend', () => {
                if (dragging) {
                    dragging.classList.remove('dragging');
                    dragging = null;
                    saveBlockOrder();
                }
            });

            blockList.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (!dragging) return;

                const items = [...blockList.querySelectorAll('.block-row[draggable="true"]:not(.dragging)')];
                const afterElement = items.find((item) => {
                    const box = item.getBoundingClientRect();
                    return e.clientY < box.top + box.height / 2;
                });

                if (afterElement) {
                    blockList.insertBefore(dragging, afterElement);
                } else {
                    blockList.appendChild(dragging);
                }
            });

            function refreshMoveButtons() {
                const rows = [...blockList.querySelectorAll('.block-row')];
                rows.forEach((row, i) => {
                    const up = row.querySelector('[data-action="move-up"]');
                    const down = row.querySelector('[data-action="move-down"]');
                    if (up) up.disabled = i === 0;
                    if (down) down.disabled = i === rows.length - 1;
                });
            }

            blockList.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-action="move-up"], [data-action="move-down"]');
                if (!btn || btn.disabled) return;
                const row = btn.closest('.block-row');
                const up = btn.dataset.action === 'move-up';
                const sibling = up ? row.previousElementSibling : row.nextElementSibling;
                if (!sibling || !sibling.classList.contains('block-row')) return;
                if (up) blockList.insertBefore(row, sibling); else blockList.insertBefore(sibling, row);
                refreshMoveButtons();
                // keep focus on the same control, or its twin if it just became disabled
                (btn.disabled ? row.querySelector(up ? '[data-action="move-down"]' : '[data-action="move-up"]') : btn)?.focus();
                const rows = [...blockList.querySelectorAll('.block-row')];
                const name = row.querySelector('.fw-semibold')?.textContent.trim() || 'Block';
                const live = document.getElementById('blockLive');
                if (live) live.textContent = name + ' moved to position ' + (rows.indexOf(row) + 1) + ' of ' + rows.length;
                saveBlockOrder();
            });

            refreshMoveButtons();
            blockList.addEventListener('dragend', () => setTimeout(refreshMoveButtons, 0));

            async function saveBlockOrder() {
                const order = [...blockList.querySelectorAll('.block-row')].map((el, idx) => ({
                    id: el.dataset.id,
                    order: (idx + 1) * 10
                }));

                try {
                    const res = await fetch(@json(route('pb.blocks.reorder', $page)), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCSRF()
                        },
                        body: JSON.stringify({
                            order
                        })
                    });

                    if (!res.ok) throw new Error('Failed to save order');
                    pbRefreshPreview();
                } catch (err) {
                    console.error('Error saving order:', err);
                    iziToast.error({ title: 'Reorder Error', message: 'Could not save block order. Refresh to retry.', position: 'topRight' });
                }
            }
        }

        /* ====== CARD HELPERS ====== */

        function extractPreviewText(block) {
            const d = block.data || {};
            const t = d.title || d.section_title || d.subtitle || d.text
                || (Array.isArray(d.items) && d.items[0]?.title)
                || (Array.isArray(d.plans) && d.plans[0]?.title)
                || (Array.isArray(d.slides) && d.slides[0]?.title)
                || '';
            return String(t).slice(0, 60) + (t.length > 60 ? '…' : '');
        }

        function updateBlockCard(block) {
            const row = blockList?.querySelector(`.block-row[data-id="${block.id}"]`);
            if (!row) { window.location.reload(); return; }
            row.querySelector('[data-action="edit"]')?.setAttribute('data-payload', encodeBlockPayload(block));
            const badge = row.querySelector('[data-pub-badge]');
            if (badge) {
                badge.className = block.is_published
                    ? 'badge bg-success-subtle text-success'
                    : 'badge bg-danger-subtle text-danger';
                badge.textContent = block.is_published ? 'Live' : 'Draft';
            }
            const preview = row.querySelector('[data-block-preview]');
            if (preview) preview.textContent = extractPreviewText(block);
        }

        /* ====== INITIALIZATION ====== */
        // Offcanvas panel variant select initialised when picker opens a block type
        const modalVariant = document.getElementById('modal_variant');
        if (modalVariant) {
            updateVariantSelect(document.getElementById('modal_type')?.value || '', modalVariant);
        }

        console.log('✅ Page Builder initialized');
    </script>
@endsection
