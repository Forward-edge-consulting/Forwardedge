<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Page, Block, Event};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Storage, Log, Route as RouteFacade};
use App\Support\PageBlueprint;
use App\Support\PageTemplates;
use App\Support\RichText;
use App\Models\Form;
use Illuminate\Validation\ValidationException;

class PageBuilderController extends Controller
{
    /* ===================== PUBLIC ===================== */

    public function show(string $slug)
    {
        $page = Page::forWebsite()->where('slug', $slug)
            ->where('status', 'published')
            ->with(['blocks' => fn($q) => $q->where('is_published', true)->whereIn('type', PageBlueprint::allowedTypes())->orderBy('order')])
            ->firstOrFail();

        seo()->forPage($page);

        return view('user.pages.dynamic', compact('page'));
    }

    /* ===================== ADMIN: PAGES ===================== */

    public function adminPages()
    {
        $pages   = Page::forWebsite()->with(['pageable'])->latest('updated_at')->paginate(20);
        $events  = Event::orderBy('title')->get(['id', 'title']);

        return view('admin.pages.page_builder.index', compact('pages', 'events'));
    }

    public function createPage()
    {
        $events  = Event::orderBy('title')->get(['id', 'title']);

        return view('admin.pages.page_builder.form', [
            'page' => new Page(),
            'events' => $events,
            'templates' => PageTemplates::all(),
        ]);
    }

    public function storePage(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:pages,slug',
            'status'     => 'required|string|in:draft,published',
            'show_on_events' => 'nullable|boolean',
            'template'   => 'nullable|string|in:' . implode(',', PageTemplates::keys()),
            'owner_type' => 'nullable|string|in:event',
            'owner_id'   => 'nullable|required_if:owner_type,event|integer|exists:events,id',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'meta_keywords'    => 'nullable|string|max:255',
            'meta_image'       => 'nullable|string|max:500',
            'meta_canonical'   => 'nullable|string|max:500',
            'meta_image_file'  => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        ]);

        $data['slug'] = $this->normalizePageSlug($data['slug'] ?? null, $data['title']);
        $this->assertUniquePageSlug($data['slug']);
        $data['show_on_events'] = ($data['owner_type'] ?? null) === 'event'
            ? $request->boolean('show_on_events', true)
            : true;

        if (!empty($data['owner_type']) && !empty($data['owner_id'])) {
            $pageableClass = match ($data['owner_type']) {
                'event'  => Event::class,
                default  => null,
            };

            if ($pageableClass && $pageableClass::find($data['owner_id'])) {
                $data['pageable_type'] = $pageableClass;
                $data['pageable_id']   = $data['owner_id'];
            }
        }

        unset($data['owner_type'], $data['owner_id']);

        $data['meta'] = $this->extractMetaFromRequest($request);
        unset($data['meta_title'], $data['meta_description'], $data['meta_keywords'], $data['meta_image'], $data['meta_canonical'], $data['meta_image_file']);

        $template = $data['template'] ?? 'blank';
        unset($data['template']);

        $page = Page::create($data);
        PageTemplates::seed($page, $template);

        return redirect()->route('pb.blocks', $page)->with('success', $template === 'blank'
            ? 'Page created. Add your first section.'
            : 'Page created from the template. Edit each section, then publish when it is ready.');
    }

    public function editPage(Page $page)
    {
        abort_unless(Page::forWebsite()->whereKey($page->id)->exists(), 404);
        $events  = Event::orderBy('title')->get(['id', 'title']);

        return view('admin.pages.page_builder.form', compact('page', 'events'));
    }

    public function updatePage(Request $request, Page $page)
    {
        abort_unless(Page::forWebsite()->whereKey($page->id)->exists(), 404);
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'status'     => 'required|string|in:draft,published',
            'show_on_events' => 'nullable|boolean',
            'owner_type' => 'nullable|string|in:event',
            'owner_id'   => 'nullable|required_if:owner_type,event|integer|exists:events,id',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'meta_keywords'    => 'nullable|string|max:255',
            'meta_image'       => 'nullable|string|max:500',
            'meta_canonical'   => 'nullable|string|max:500',
            'meta_image_file'  => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        ]);

        $data['slug'] = $this->normalizePageSlug($data['slug'] ?? null, $data['title']);
        $this->assertUniquePageSlug($data['slug'], $page->id);
        $data['show_on_events'] = ($data['owner_type'] ?? null) === 'event'
            ? $request->boolean('show_on_events', true)
            : true;

        if (!empty($data['owner_type']) && !empty($data['owner_id'])) {
            $pageableClass = match ($data['owner_type']) {
                'event'  => Event::class,
                default  => null,
            };

            if ($pageableClass && $pageableClass::find($data['owner_id'])) {
                $data['pageable_type'] = $pageableClass;
                $data['pageable_id']   = $data['owner_id'];
            } else {
                $data['pageable_type'] = null;
                $data['pageable_id']   = null;
            }
        } else {
            $data['pageable_type'] = null;
            $data['pageable_id']   = null;
        }

        unset($data['owner_type'], $data['owner_id']);

        $data['meta'] = $this->extractMetaFromRequest($request);
        unset($data['meta_title'], $data['meta_description'], $data['meta_keywords'], $data['meta_image'], $data['meta_canonical'], $data['meta_image_file']);

        $page->update($data);

        return back()->with('success', 'Page updated successfully.');
    }

    public function destroyPage(Page $page)
    {
        abort_unless(Page::forWebsite()->whereKey($page->id)->exists(), 404);
        $page->delete();
        return redirect()->route('pb.pages')->with('success', 'Page deleted successfully.');
    }

    /* ===================== ADMIN: BLOCKS ===================== */

    public function adminBlocks(Page $page)
    {
        abort_unless(Page::forWebsite()->whereKey($page->id)->exists(), 404);
        $blocks = $page->blocks()
            ->whereIn('type', PageBlueprint::allowedTypes())
            ->orderBy('order')
            ->get()
            ->map(function ($b) {
                $b->data = is_array($b->data) ? $b->data : json_decode($b->data ?? '{}', true);
                return $b;
            });

        $blockTypes = PageBlueprint::allowedTypes();

        $variants = [
            'page_header'  => ['default'],
            'rich_text'    => ['default'],
            'stats'        => ['default'],
            'form'         => ['default'],
            'events_grid'  => ['default'],
            'event_facts'  => ['default'],
            'cta_band'     => ['default'],
            'faq_list'     => ['default'],
            'feature_grid' => ['default'],
            'steps'        => ['default'],
            'quote_list'   => ['default'],
            'contact_info' => ['default'],
            'hero'         => ['default', 'minimal', 'with_video'],
            'hero2'        => ['default'],
            'hero3'        => ['default'],
            'hero4'        => ['default'],
            'program_overview' => ['default'],
            'overview'     => ['cards', 'timeline'],
            'overview2'    => ['default'],
            'form_dark'    => ['default'],
            'form_light'   => ['default'],
            'about'        => ['default', 'split'],
            'about2'       => ['default'],
            'sections'     => ['default', 'carousel'],
            'sections2'    => ['default'],
            'marquees'     => ['default'],
            'logo_slider'  => ['default'],
            'gallary'      => ['grid', 'masonry'],
            'testimonial'  => ['slider', 'grid', 'featured'],
            'faq'          => ['accordion', 'tabs'],
            'closing_cta'  => ['default', 'split'],
        ];

        $allowedRouteParams = ['event', 'event_id', 'page', 'page_id', 'slug'];

        $internalRoutes = collect(RouteFacade::getRoutes())
            ->filter(fn($route) => $route->getName() && in_array('GET', $route->methods()))
            ->reject(function ($route) {
                $uri = ltrim($route->uri(), '/');
                $name = $route->getName();

                return Str::startsWith($uri, 'ctrl-panel-v2')
                    || Str::startsWith($uri, 'admin')
                    || Str::startsWith($name, 'admin.')
                    || Str::startsWith($name, 'pb.')
                    || Str::contains($name, 'admin');
            })
            ->map(function ($route) {
                $uri  = $route->uri();
                $path = '/' . ltrim($uri, '/');
                if ($path === '//') {
                    $path = '/';
                }

                preg_match_all('/\{([^}]+)\}/', $uri, $matches);
                $placeholders = $matches[1] ?? [];

                return [
                    'name'         => $route->getName(),
                    'uri'          => $uri,
                    'path'         => $path,
                    'needs_params' => !empty($placeholders),
                    'placeholders' => $placeholders,
                ];
            })
            ->filter(function ($route) use ($allowedRouteParams) {
                if (empty($route['placeholders'])) {
                    return true;
                }

                return collect($route['placeholders'])
                    ->every(fn($ph) => in_array($ph, $allowedRouteParams, true));
            })
            ->sortBy('name')
            ->values();

        $routeBindingOptions = $this->buildRouteBindingOptions();

        $iconOptions = $this->loadBootstrapIcons();

        // Real forms to pick from in a "Form" section, and the event types an events list can filter by.
        $formOptions = Form::orderBy('name')->get(['id', 'name', 'slug'])
            ->reject(fn ($f) => $f->slug === 'page-forms')->values();
        $eventTypes = \App\Models\EventCategory::orderBy('sort_order')->orderBy('name')->pluck('name', 'id');
        $pickerTypes = PageBlueprint::pickerTypes();

        return view('admin.pages.page_builder.blocks.form', compact(
            'page',
            'blocks',
            'blockTypes',
            'variants',
            'internalRoutes',
            'routeBindingOptions',
            'iconOptions',
            'formOptions',
            'eventTypes',
            'pickerTypes'
        ));
    }

    public function storeBlock(Request $request, Page $page)
    {
        abort_unless(Page::forWebsite()->whereKey($page->id)->exists(), 404);
        $type = (string) $request->input('type');
        PageBlueprint::ensureValidType($type);

        // Get and clean data
        $rawData = $request->input('data', []);
        try {
            $cleanedData = $this->cleanBlockData($rawData, $type);
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'errors' => ['data' => $e->getMessage()]], 422);
            }
            return back()
                ->withErrors(['data' => $e->getMessage()])
                ->withInput();
        }

        // Merge back for validation
        $request->merge(['data' => $cleanedData]);

        // Validate
        $validated = $request->validate(array_merge(
            PageBlueprint::prefixedRulesFor($type),
            [
                'variant'      => 'nullable|string|max:100',
                'is_published' => 'nullable|boolean',
            ]
        ));

        $payload = $validated['data'] ?? [];

        // Handle file uploads
        $payload = $this->handleBlockFileUploads($request, $payload, $type);

        // Create block
        $nextOrder = ((int) $page->blocks()->max('order')) + 10;

        $block = $page->blocks()->create([
            'type'         => $type,
            'variant'      => $request->input('variant'),
            'data'         => $payload,
            'order'        => $nextOrder,
            'is_published' => (bool) $request->boolean('is_published', true),
        ]);

        if ($request->expectsJson()) {
            $block->refresh();
            return response()->json([
                'ok'      => true,
                'message' => "Block '{$type}' created successfully.",
                'block'   => $block,
            ]);
        }

        return redirect()
            ->route('pb.blocks', $page)
            ->with('success', "Block '{$type}' created successfully.");
    }

    public function updateBlock(Request $request, Block $block)
    {
        abort_unless(Page::forWebsite()->whereKey($block->page_id)->exists(), 404);
        $type = (string) $request->input('type');
        PageBlueprint::ensureValidType($type);

        // Get and clean data
        $rawData = $request->input('data', []);
        try {
            $cleanedData = $this->cleanBlockData($rawData, $type);
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'errors' => ['data' => $e->getMessage()]], 422);
            }
            return back()
                ->withErrors(['data' => $e->getMessage()])
                ->withInput();
        }

        // Merge back for validation
        $request->merge(['data' => $cleanedData]);

        // Validate
        $validated = $request->validate(array_merge(
            PageBlueprint::prefixedRulesFor($type),
            [
                'variant'      => 'nullable|string|max:100',
                'is_published' => 'nullable|boolean',
            ]
        ));

        // Merge with existing data to preserve unchanged fields
        $payload = array_merge(
            is_array($block->data) ? $block->data : [],
            $validated['data'] ?? []
        );

        // Handle file uploads
        $payload = $this->handleBlockFileUploads($request, $payload, $type, $block->data);

        // Update block
        $block->update([
            'type'         => $type,
            'variant'      => $request->input('variant'),
            'data'         => $payload,
            'is_published' => (bool) $request->boolean('is_published', $block->is_published),
        ]);

        if ($request->expectsJson()) {
            $block->refresh();
            return response()->json([
                'ok'      => true,
                'message' => "Block '{$type}' updated successfully.",
                'block'   => $block,
            ]);
        }

        return redirect()
            ->route('pb.blocks', $block->page)
            ->with('success', "Block '{$type}' updated successfully.");
    }

    public function destroyBlock(Block $block)
    {
        abort_unless(Page::forWebsite()->whereKey($block->page_id)->exists(), 404);
        $this->deleteBlockFiles($block->data ?? []);
        $page = $block->page;
        $block->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Block deleted.']);
        }

        return redirect()->route('pb.blocks', $page)->with('success', 'Block deleted.');
    }

    public function togglePublish(Request $request, Block $block)
    {
        abort_unless(Page::forWebsite()->whereKey($block->page_id)->exists(), 404);
        PageBlueprint::ensureValidType($block->type);
        $block->update(['is_published' => $request->boolean('is_published')]);
        return response()->json(['ok' => true, 'is_published' => $block->is_published]);
    }

    public function cloneBlock(Block $block)
    {
        abort_unless(Page::forWebsite()->whereKey($block->page_id)->exists(), 404);
        PageBlueprint::ensureValidType($block->type);
        $page = $block->page;
        $nextOrder = ((int) $page->blocks()->max('order')) + 10;

        $newBlock = $page->blocks()->create([
            'type'         => $block->type,
            'variant'      => $block->variant,
            'data'         => $block->data,
            'order'        => $nextOrder,
            'is_published' => false,
        ]);

        $newBlock->refresh();

        return response()->json([
            'ok'      => true,
            'message' => 'Block duplicated as Draft.',
            'block'   => $newBlock,
        ]);
    }

    public function previewPage(Page $page)
    {
        abort_unless(Page::forWebsite()->whereKey($page->id)->exists(), 404);
        $page->load(['blocks' => fn($q) => $q->whereIn('type', PageBlueprint::allowedTypes())->orderBy('order')]);
        seo()->forPage($page);
        $pbEdit = request()->boolean('pb_edit');
        return view('user.pages.dynamic', compact('page', 'pbEdit'));
    }

    public function reorderBlocks(Request $request, Page $page)
    {
        abort_unless(Page::forWebsite()->whereKey($page->id)->exists(), 404);
        $order = $request->input('order');

        // Defence-in-depth: abort if any submitted ID doesn't belong to this page
        $allIds = collect($order)->map(fn($item) => is_array($item) ? ($item['id'] ?? null) : $item)
            ->filter()->map(fn($id) => (int) $id);
        if ($allIds->isNotEmpty()) {
            $validIds = $page->blocks()->whereIn('id', $allIds)->pluck('id');
            if ($allIds->diff($validIds)->isNotEmpty()) {
                abort(403, 'Block IDs do not belong to this page.');
            }
        }

        DB::transaction(function () use ($order, $page) {
            // Case A: [{id: 123, order: 10}, {id: 456, order: 20}]
            if (is_array($order) && isset($order[0]) && is_array($order[0]) && array_key_exists('id', $order[0])) {
                foreach ($order as $row) {
                    $id  = $row['id'] ?? null;
                    $ord = (int) ($row['order'] ?? 0);
                    if ($id) {
                        Block::where('page_id', $page->id)->whereKey($id)->update(['order' => $ord ?: 10]);
                    }
                }
                return;
            }

            // Case B: [123, 456, 789] → assign 10,20,30...
            if (is_array($order) && array_is_list($order)) {
                $pos = 10;
                foreach ($order as $id) {
                    Block::where('page_id', $page->id)->whereKey($id)->update(['order' => $pos]);
                    $pos += 10;
                }
                return;
            }

            // Case C: {"123": 10, "456": 20}
            if (is_array($order)) {
                foreach ($order as $id => $ord) {
                    Block::where('page_id', $page->id)->whereKey($id)->update(['order' => (int) $ord]);
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Block order saved successfully.');
    }

    public function json(Page $page)
    {
        abort_unless(Page::forWebsite()->whereKey($page->id)->exists(), 404);
        $blocks = $page->blocks()
            ->whereIn('type', PageBlueprint::allowedTypes())
            ->orderBy('order')
            ->get()
            ->map(function ($b) {
                $b->data = is_array($b->data) ? $b->data : json_decode($b->data ?? '{}', true);
                return $b;
            });

        return response()->json($blocks);
    }

    /* ===================== PRIVATE HELPER METHODS ===================== */

    private function buildRouteBindingOptions(): array
    {
        $options = [];

        $eventRecords = Event::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $eventOptions = $eventRecords
            ->map(fn($event) => [
                'value' => (string) $event->id,
                'label' => $event->title ?: "Event #{$event->id}",
                'hint'  => "ID #{$event->id}",
            ])
            ->all();

        if (!empty($eventOptions)) {
            $options['event'] = $eventOptions;
            $options['event_id'] = $eventOptions;
        }

        $pageRecords = Page::forWebsite()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $pageOptions = $pageRecords
            ->map(fn($page) => [
                'value' => (string) $page->id,
                'label' => $page->title ?: "Page #{$page->id}",
                'hint'  => $page->slug ? "Slug: {$page->slug}" : null,
            ])
            ->all();

        if (!empty($pageOptions)) {
            $options['page'] = $pageOptions;
            $options['page_id'] = $pageOptions;
        }

        $slugOptions = collect();


        $slugOptions = $slugOptions->merge(
            $eventRecords
                ->filter(fn($event) => filled($event->slug))
                ->map(fn($event) => [
                    'value' => $event->slug,
                    'label' => ($event->title ?: "Event #{$event->id}") . ' • Event',
                    'hint'  => "Slug: {$event->slug}",
                ])
        );

        $slugOptions = $slugOptions->merge(
            $pageRecords
                ->filter(fn($page) => filled($page->slug))
                ->map(fn($page) => [
                    'value' => $page->slug,
                    'label' => ($page->title ?: "Page #{$page->id}") . ' • Page',
                    'hint'  => "Slug: {$page->slug}",
                ])
        );

        $slugOptions = $slugOptions->unique('value')->values()->all();
        if (!empty($slugOptions)) {
            $options['slug'] = $slugOptions;
        }

        return array_filter($options, fn($list) => !empty($list));
    }

    /**
     * Clean and normalize block data before validation
     */
    private function cleanBlockData(array $data, string $type): array
    {
        // Remove empty items from arrays
        $data = $this->removeEmptyArrayItems($data);

        if ($type === 'rich_text' && isset($data['body'])) {
            $data['body'] = RichText::cleanDocument((string) $data['body']);
        }

        // Type-specific cleaning
        return match ($type) {
            'closing_cta' => $this->normalizeClosingCta($data),
            'hero3'       => $this->cleanHero3Data($data),
            'form_dark',
            'form_light'  => $this->normalizeFormFields($data),
            default       => $data,
        };
    }

    /**
     * Recursively remove empty items from arrays
     */
    private function removeEmptyArrayItems(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $cleaned = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recursively clean nested arrays
                $value = $this->removeEmptyArrayItems($value);

                // Check if this is an indexed array
                if (array_is_list($value)) {
                    // Remove empty items from lists
                    $value = array_filter($value, function ($item) {
                        if (is_string($item)) {
                            return trim($item) !== '';
                        }
                        if (is_array($item)) {
                            // For objects, check if any non-empty values exist
                            return !empty(array_filter($item, function ($v) {
                                return is_string($v) ? trim($v) !== '' : !empty($v);
                            }));
                        }
                        return !empty($item);
                    });

                    // Re-index array
                    $value = array_values($value);
                }

                $cleaned[$key] = $value;
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Normalize contact form fields
     */

    private function normalizeFormFields(array $data): array
    {
        if (!isset($data['fields']) || !is_array($data['fields'])) {
            $data['fields'] = [];
            return $data;
        }

        $normalized = [];

        foreach ($data['fields'] as $field) {
            $label = trim((string) ($field['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $name = trim((string) ($field['name'] ?? '')) ?: $label;
            $slug = Str::slug($name, '_');
            if ($slug === '') {
                $slug = 'field_' . (count($normalized) + 1);
            }

            $type = in_array($field['type'] ?? '', ['text', 'email', 'tel', 'textarea'], true)
                ? $field['type']
                : 'text';

            $normalized[] = [
                'label'       => $label,
                'name'        => $slug,
                'type'        => $type,
                'placeholder' => $field['placeholder'] ?? '',
                'required'    => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'width'       => ($field['width'] ?? 'full') === 'half' ? 'half' : 'full',
            ];
        }

        $data['fields'] = array_values($normalized);

        $mode = $data['email_mode'] ?? null;
        $allowedModes = ['newsletter', 'thank_you', 'custom', 'none'];
        $data['email_mode'] = in_array($mode, $allowedModes, true) ? $mode : null;

        $subject = trim((string) ($data['email_subject'] ?? ''));
        $body = trim((string) ($data['email_body'] ?? ''));

        $data['email_subject'] = $subject !== '' ? $subject : null;
        $data['email_body'] = $body !== '' ? $body : null;

        return $data;
    }


    /**
     * Clean hero3 title segments
     */
    private function cleanHero3Data(array $data): array
    {
        if (empty($data['title']) && !empty($data['title_segments']) && is_array($data['title_segments'])) {
            $data['title'] = trim(implode(' ', array_filter(array_map(
                fn($v) => is_string($v) ? trim($v) : '',
                $data['title_segments']
            ))));
        }

        $data['title'] = isset($data['title']) ? trim((string) $data['title']) : null;
        $data['description'] = isset($data['description']) ? trim((string) $data['description']) : null;

        if (!empty($data['icon_bi']) && is_string($data['icon_bi'])) {
            $data['icon_bi'] = trim($data['icon_bi']);
        } else {
            unset($data['icon_bi']);
        }

        unset($data['title_segments'], $data['highlight_index']);

        return $data;
    }

    /**
     * Normalize closing CTA - handle both new and legacy key formats
     */
    private function normalizeClosingCta(array $data): array
    {
        if (!isset($data['ctas']) || !is_array($data['ctas'])) {
            return $data;
        }

        $out = [];
        foreach ($data['ctas'] as $cta) {
            if (!is_array($cta)) continue;

            // Skip completely empty CTAs
            if (
                empty($cta['link_text']) && empty($cta['text']) &&
                empty($cta['link']) && empty($cta['href'])
            ) {
                continue;
            }

            // Map legacy keys to new schema
            $out[] = [
                'link_text' => $cta['link_text'] ?? ($cta['text'] ?? null),
                'link'      => $cta['link'] ?? ($cta['href'] ?? null),
            ];
        }

        $data['ctas'] = array_values($out);
        return $data;
    }

    /**
     * Handle file uploads for all block types
     */
    private function handleBlockFileUploads(
        Request $request,
        array $payload,
        string $type,
        ?array $existingData = null
    ): array {
        $existingData = is_array($existingData) ? $existingData : [];

        // Single-file blocks
        $singleFileBlocks = [
            'hero'   => ['banner_image' => 'blocks/hero'],
            'hero2'  => ['hero_image' => 'blocks/hero2', 'banner_image' => 'blocks/hero2'],
            'hero3'  => ['banner_image' => 'blocks/hero3', 'image' => 'blocks/hero3', 'hero_image' => 'blocks/hero3', 'verified_icon' => 'blocks/hero3'],
            'hero4'  => ['hero_image' => 'blocks/hero4'],
            'page_header' => ['image' => 'blocks/page_header'],
            'about'  => ['banner_left' => 'blocks/about'],
            'about2' => ['about_image' => 'blocks/about2'],
        ];

        if (isset($singleFileBlocks[$type])) {
            foreach ($singleFileBlocks[$type] as $field => $path) {
                if ($request->hasFile($field)) {
                    // Delete old file
                    if (!empty($existingData[$field])) {
                        Storage::disk('public')->delete($existingData[$field]);
                    }
                    $payload[$field] = $request->file($field)->store($path, 'public');
                } elseif (!empty($existingData[$field])) {
                    // Keep existing
                    $payload[$field] = $existingData[$field];
                }
            }
        }

        // Array-based file blocks
        $arrayFileBlocks = [
            'about'      => ['cards' => ['image' => 'blocks/about/cards'], 'tiles' => ['bg' => 'blocks/about/tiles']],
            'sections'   => ['items' => ['image' => 'blocks/sections']],
            'sections2'  => ['items' => ['image' => 'blocks/sections2']],
            'overview2'  => ['items' => ['image' => 'blocks/program_includes']],
            'marquees'   => ['slides' => ['image' => 'blocks/marquees']],
            'gallary'    => ['items' => ['image' => 'blocks/gallery']],
            'testimonial' => ['items' => ['photo' => 'blocks/testimonials']],
            'logo_slider' => ['logos' => ['image' => 'blocks/brands'], 'brands' => ['image' => 'blocks/brands']],
        ];

        if (isset($arrayFileBlocks[$type])) {
            foreach ($arrayFileBlocks[$type] as $arrayKey => $fileFields) {
                if (isset($payload[$arrayKey]) && is_array($payload[$arrayKey])) {
                    foreach ($payload[$arrayKey] as $i => $item) {
                        foreach ($fileFields as $fileField => $path) {
                            if ($request->hasFile("{$arrayKey}.{$i}.{$fileField}")) {
                                // Delete old
                                if (!empty($existingData[$arrayKey][$i][$fileField] ?? null)) {
                                    Storage::disk('public')->delete($existingData[$arrayKey][$i][$fileField]);
                                }
                                $payload[$arrayKey][$i][$fileField] = $request->file("{$arrayKey}.{$i}.{$fileField}")
                                    ->store($path, 'public');
                            } elseif (!empty($existingData[$arrayKey][$i][$fileField] ?? null)) {
                                // Keep existing
                                $payload[$arrayKey][$i][$fileField] = $existingData[$arrayKey][$i][$fileField];
                            }
                        }
                    }
                }
            }
        }

        return $payload;
    }

    /**
     * Delete files associated with a block (recursively)
     */
    private function deleteBlockFiles(?array $data): void
    {
        if (!$data) return;

        $fileKeys = ['banner_image', 'banner_left', 'image', 'bg', 'photo', 'hero_image', 'about_image', 'verified_icon'];

        // Delete top-level files
        foreach ($fileKeys as $key) {
            if (!empty($data[$key]) && is_string($data[$key])) {
                Storage::disk('public')->delete($data[$key]);
            }
        }

        // Delete nested array files
        $nestedKeys = ['cards', 'tiles', 'items', 'slides', 'logos', 'brands'];
        foreach ($nestedKeys as $nestedKey) {
            if (!empty($data[$nestedKey]) && is_array($data[$nestedKey])) {
                foreach ($data[$nestedKey] as $item) {
                    if (is_array($item)) {
                        $this->deleteBlockFiles($item);
                    }
                }
            }
        }
    }

    private function extractMetaFromRequest(Request $request): ?array
    {
        $metaImage = $request->input('meta_image');

        if ($request->hasFile('meta_image_file')) {
            $path = $request->file('meta_image_file')->store('pages/meta', 'public');
            $metaImage = Storage::url($path);
        }

        $meta = array_filter([
            'title'       => $request->input('meta_title'),
            'description' => $request->input('meta_description'),
            'keywords'    => $request->input('meta_keywords'),
            'image'       => $metaImage,
            'canonical'   => $request->input('meta_canonical'),
        ], fn($value) => filled($value));

        return empty($meta) ? null : $meta;
    }

    private function loadBootstrapIcons(): array
    {
        $path = public_path('backend/assets/vendor/bootstrap-icons/bootstrap-icons.css');

        if (!file_exists($path)) {
            return [];
        }

        $css = file_get_contents($path);
        if ($css === false) {
            return [];
        }

        preg_match_all('/\\.bi-([a-z0-9-]+)::before/', $css, $matches);
        $names = $matches[1] ?? [];

        return collect($names)
            ->unique()
            ->map(function ($name) {
                $readable = Str::of($name)->replace(['-', '_'], ' ')->title();
                return [
                    'value' => 'bi-' . $name,
                    'label' => "{$readable} ({$name})",
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function normalizePageSlug(?string $value, string $fallback): string
    {
        $base = trim($value ?? '');
        if ($base === '') {
            $base = $fallback;
        }

        $slug = Str::lower($base);
        $slug = preg_replace('/[^a-z0-9\.\-]+/i', '-', $slug) ?: '';
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-.');

        return $slug !== '' ? $slug : Str::slug($fallback);
    }

    private function assertUniquePageSlug(string $slug, ?int $ignoreId = null): void
    {
        $query = Page::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'This slug is already in use once formatting is applied.',
            ]);
        }
    }
}
