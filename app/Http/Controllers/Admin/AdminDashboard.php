<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Event;
use App\Models\FormSubmission;
use App\Models\Page;

class AdminDashboard extends Controller
{
    public function index()
    {
        $upcoming = Event::with(['category', 'pages'])
            ->where('status', 'published')->notPast()->orderBy('start_date')->take(5)->get();
        $nextEvent = $upcoming->first();
        $comingUp = $upcoming->slice(1, 4);

        $pagesLive = Page::forWebsite()->where('status', 'published')->count();
        $pagesDraft = Page::forWebsite()->where('status', '!=', 'published')->count();
        $postsLive = Blog::published()->count();
        $postsDraft = Blog::where('is_published', false)->count();

        $kpis = [
            'leads' => FormSubmission::readyToExport()->count(),
            'upcoming' => Event::where('status', 'published')->notPast()->count(),
            'postsLive' => $postsLive,
            'postsDraft' => $postsDraft,
            'pagesLive' => $pagesLive,
            'pagesDraft' => $pagesDraft,
            'weekResponses' => FormSubmission::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        // Things a person can actually go and fix.
        $attention = collect([
            ['n' => $postsDraft, 'icon' => 'bi-journal-text', 'tone' => 'yellow',
                'title' => 'unpublished blog ' . str('post')->plural($postsDraft), 'sub' => 'Drafts waiting to be finished or published.',
                'url' => route('admin.blogs.index'), 'cta' => 'Open blog'],
            ['n' => Event::where('status', 'published')->notPast()->doesntHave('pages')->count(), 'icon' => 'bi-calendar-event', 'tone' => 'blue',
                'title' => 'upcoming events without a page', 'sub' => 'Build a page so each one tells its story.',
                'url' => route('admin.events.list', ['when' => 'upcoming']), 'cta' => 'Review events'],
            ['n' => Event::where('status', 'published')->past()->whereNull('highlights')->count(), 'icon' => 'bi-trophy', 'tone' => 'green',
                'title' => 'past events with no highlights', 'sub' => 'Add attendees and speakers to show them off on the Past wall.',
                'url' => route('admin.events.list', ['when' => 'past']), 'cta' => 'Add numbers'],
            ['n' => $pagesDraft, 'icon' => 'bi-file-earmark-richtext', 'tone' => 'navy',
                'title' => 'draft ' . str('page')->plural($pagesDraft), 'sub' => 'Built but not live yet.',
                'url' => route('pb.pages'), 'cta' => 'Open pages'],
            ['n' => $kpis['leads'], 'icon' => 'bi-inbox', 'tone' => 'yellow',
                'title' => str('contact')->plural($kpis['leads']) . ' ready to export to Brevo', 'sub' => 'Only people who agreed to be emailed.',
                'url' => route('admin.responses.index', ['status' => 'new']), 'cta' => 'Export'],
        ])->filter(fn ($a) => $a['n'] > 0)->values();

        return view('admin.pages.dashboard', [
            'kpis' => $kpis,
            'attention' => $attention,
            'nextEvent' => $nextEvent,
            'comingUp' => $comingUp,
            'recentResponses' => FormSubmission::with('form:id,name')->latest()->take(6)->get(),
            'greeting' => match (true) {
                now()->hour < 12 => 'Good morning',
                now()->hour < 17 => 'Good afternoon',
                default => 'Good evening',
            },
            'firstName' => explode(' ', trim(auth()->user()?->name ?: 'there'))[0],
        ]);
    }
}
