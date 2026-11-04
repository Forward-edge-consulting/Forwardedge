<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Form;
use App\Models\Page;
use App\Support\PageTemplates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminEventController extends Controller
{
    public function index(Request $request)
    {
        $when = in_array($request->input('when'), ['upcoming', 'past'], true) ? $request->input('when') : null;

        $events = Event::with(['category', 'pages'])
            ->when($when === 'upcoming', fn ($q) => $q->notPast())
            ->when($when === 'past', fn ($q) => $q->past())
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('start_date')
            ->get();

        return view('admin.pages.events.index', [
            'events' => $events,
            'categories' => EventCategory::orderBy('sort_order')->orderBy('name')->get(),
            'when' => $when,
        ]);
    }

    public function create()
    {
        $event = new Event(['status' => 'draft', 'cta_type' => 'none', 'highlights' => []]);

        return view('admin.pages.events.form', $this->formData($event));
    }

    public function store(Request $request)
    {
        $event = new Event();
        $this->fill($request, $event);
        $event->save();

        return redirect()->route('admin.events.dashboard', $event)
            ->with('success', 'Event saved. Next, build its page so it tells the story.');
    }

    /** The event screen: details, call to action and the page. */
    public function dashboard(Event $event)
    {
        return view('admin.pages.events.form', $this->formData($event));
    }

    public function update(Request $request, Event $event)
    {
        $this->fill($request, $event);
        $event->save();

        return redirect()->route('admin.events.dashboard', $event)->with('success', 'Event saved.');
    }

    public function destroy(Event $event)
    {
        foreach ($event->pages as $page) {
            $page->update(['pageable_type' => null, 'pageable_id' => null, 'status' => 'draft']);
        }
        if ($event->thumbnail) {
            Storage::disk('public')->delete($event->thumbnail);
        }
        $event->delete();

        return redirect()->route('admin.events.list')->with('success', 'Event deleted. Its page was kept as a draft.');
    }

    /** Make a page for this event from a template (upcoming or recap), then open it in the page builder. */
    public function createPage(Request $request, Event $event)
    {
        $template = $request->validate(['template' => 'required|in:event_upcoming,event_recap'])['template'];

        if ($event->pages()->exists()) {
            return back()->with('error', 'This event already has a page.');
        }

        $base = Str::slug($event->title) ?: 'event';
        $slug = $base;
        $i = 2;
        while (Page::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $page = $event->pages()->create(['title' => $event->title, 'slug' => $slug, 'status' => 'draft', 'template' => 'default', 'show_on_events' => true]);
        PageTemplates::seed($page, $template, $this->pageTexts($event));

        return redirect()->route('pb.blocks', $page)
            ->with('success', 'Page created as a draft. Edit each section, then publish it.');
    }

    /** After the event: add "the day in numbers" and "what happened" to the existing page. */
    public function addRecap(Event $event)
    {
        $page = $event->pages()->latest('id')->first();
        abort_unless($page, 404);

        PageTemplates::append($page, 'recap_sections');

        return redirect()->route('pb.blocks', $page)->with('success', 'Recap sections added at the end of the page. Fill in the numbers and the story.');
    }

    // ---------------------------------------------------------------------

    protected function formData(Event $event): array
    {
        return [
            'event' => $event,
            'categories' => EventCategory::orderBy('sort_order')->orderBy('name')->get(),
            'forms' => Form::orderBy('name')->get(['id', 'name', 'slug'])->reject(fn ($f) => in_array($f->slug, ['page-forms', 'event-registrations']))->values(),
            'page' => $event->exists ? $event->pages()->latest('id')->first() : null,
        ];
    }

    protected function fill(Request $request, Event $event): void
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('events', 'slug')->ignore($event->id)],
            'category_id' => 'nullable|integer|exists:event_categories,id',
            'status' => 'required|in:draft,published,cancelled',
            'short_description' => 'nullable|string|max:500',
            'location' => 'required|string|max:255',
            'venue' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'highlights_json' => 'nullable|string',
            'cta_type' => 'required|in:none,interest,link,contact',
            'cta_label' => 'nullable|string|max:60',
            'cta_url' => ['nullable', 'string', 'max:500', 'required_if:cta_type,link', function ($attr, $value, $fail) {
                if (filled($value) && !filter_var($value, FILTER_VALIDATE_URL) && !str_starts_with($value, '/')) {
                    $fail('Enter a full web address, such as https://…');
                }
            }],
            'cta_form_id' => 'nullable|integer|exists:forms,id',
            'type' => 'nullable|string|max:40', // older callers; the category is the source of truth
        ]);

        $event->title = $data['title'];
        $event->slug = $this->uniqueSlug($data['slug'] ?? '', $data['title'], $event);
        $event->status = $data['status'];
        $event->short_description = $data['short_description'] ?? null;
        $event->location = $data['location'];
        $event->venue = $data['venue'] ?? null;
        $event->start_date = $data['start_date'];
        // The table needs an end. A one-day event simply ends when its start day does.
        $event->end_date = $data['end_date'] ?? \Illuminate\Support\Carbon::parse($data['start_date'])->endOfDay();

        $category = !empty($data['category_id']) ? EventCategory::find($data['category_id']) : null;
        $event->category_id = $category?->id;
        $event->type = $category?->slug ?? ($data['type'] ?? $event->type ?? 'conference');

        $event->highlights = $this->highlights($data['highlights_json'] ?? '');

        $event->cta_type = $data['cta_type'];
        $event->cta_label = $data['cta_type'] === 'none' ? null : ($data['cta_label'] ?? null);
        $event->cta_url = $data['cta_type'] === 'link' ? ($data['cta_url'] ?? null) : null;
        $event->cta_form_id = $data['cta_type'] === 'interest' ? ($data['cta_form_id'] ?? PageTemplates::interestForm()->id) : null;

        if ($request->hasFile('thumbnail')) {
            if ($event->thumbnail) {
                Storage::disk('public')->delete($event->thumbnail);
            }
            $event->thumbnail = $request->file('thumbnail')->store('events/thumbnails', 'public');
        }
    }

    protected function highlights(string $json): ?array
    {
        $rows = json_decode($json, true);
        if (!is_array($rows)) {
            return null;
        }

        $clean = collect($rows)->map(fn ($r) => [
            'value' => Str::limit(trim(strip_tags((string) ($r['value'] ?? ''))), 24, ''),
            'label' => Str::limit(trim(strip_tags((string) ($r['label'] ?? ''))), 90, ''),
        ])->filter(fn ($r) => $r['value'] !== '' && $r['label'] !== '')->take(6)->values()->all();

        return $clean ?: null;
    }

    protected function uniqueSlug(string $slug, string $title, Event $event): string
    {
        $base = Str::slug($slug) ?: (Str::slug($title) ?: 'event');
        $candidate = $base;
        $i = 2;
        while (Event::where('slug', $candidate)->when($event->exists, fn ($q) => $q->where('id', '!=', $event->id))->exists()) {
            $candidate = $base . '-' . $i++;
        }

        return $candidate;
    }

    /** The template placeholders become this event's real title and summary. */
    protected function pageTexts(Event $event): array
    {
        return [
            'title' => $event->title,
            'subtitle' => $event->short_description,
            'highlights' => $event->highlights,
        ];
    }
}
