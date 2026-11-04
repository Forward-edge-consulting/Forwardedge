<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Page;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /** Upcoming and past events, filtered by category. Past events are the showcase. */
    public function index(Request $request)
    {
        $upcomingCount = Event::visibleOnSite()->notPast()->count();
        $pastCount = Event::visibleOnSite()->past()->count();

        $when = $request->input('when');
        if (!in_array($when, ['upcoming', 'past'], true)) {
            $when = $upcomingCount > 0 || $pastCount === 0 ? 'upcoming' : 'past';
        }
        $scope = $when === 'past' ? 'past' : 'notPast';

        $categories = EventCategory::orderBy('sort_order')->orderBy('name')
            ->withCount(['events as n' => fn ($q) => $q->visibleOnSite()->{$scope}()])
            ->get()->filter(fn ($c) => $c->n > 0)->values();

        $active = $request->filled('category') ? $categories->firstWhere('slug', $request->input('category')) : null;

        $events = Event::visibleOnSite()->{$scope}()->with('category')
            ->when($active, fn ($q) => $q->where('category_id', $active->id))
            ->when($when === 'past', fn ($q) => $q->orderByDesc('start_date'), fn ($q) => $q->orderBy('start_date'))
            ->paginate(12)->withQueryString();

        return view('user.pages.events_training', compact('events', 'categories', 'active', 'when', 'upcomingCount', 'pastCount'));
    }

    public function show($slug)
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->with(['category', 'ctaForm'])->firstOrFail();

        if ($page = Page::where('pageable_type', Event::class)->where('pageable_id', $event->id)
            ->where('status', 'published')->orderByDesc('updated_at')->first()) {
            return redirect()->route('page.show', $page->slug);
        }

        return view('user.pages.events_details', compact('event'));
    }
}
