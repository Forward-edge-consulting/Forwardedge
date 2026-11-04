<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Event;
use App\Models\Page;
use App\Models\Service;

class SitemapController extends Controller
{
    public function index()
    {
        $blogs    = Blog::query()->published()->get(['slug', 'updated_at']);
        $events   = Event::query()->where('status', 'published')->get(['slug', 'updated_at']);
        $services = Service::query()->get(['slug', 'updated_at']);
        $pages    = Page::forWebsite()->where('status', 'published')->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', compact('blogs', 'events', 'services', 'pages'))
            ->header('Content-Type', 'application/xml');
    }
}
