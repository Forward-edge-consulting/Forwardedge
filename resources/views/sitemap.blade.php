<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Homepage --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- Static pages --}}
    @foreach ([
        ['route' => 'about',   'freq' => 'monthly', 'priority' => '0.7'],
        ['route' => 'consultancy', 'freq' => 'monthly', 'priority' => '0.8'],
        ['route' => 'contact', 'freq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'events.index', 'freq' => 'weekly', 'priority' => '0.8'],
        ['route' => 'gallery', 'freq' => 'weekly',  'priority' => '0.6'],
    ] as $page)
    <url>
        <loc>{{ route($page['route']) }}</loc>
        <changefreq>{{ $page['freq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
    @endforeach

    {{-- Blog posts --}}
    @foreach ($blogs as $blog)
    <url>
        <loc>{{ route('blogs.show', $blog->slug) }}</loc>
        <lastmod>{{ $blog->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Events --}}
    @foreach ($events as $event)
    <url>
        <loc>{{ route('events.show', $event->slug) }}</loc>
        <lastmod>{{ $event->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Services --}}
    @foreach ($services as $service)
    <url>
        <loc>{{ route('services.show', $service->slug) }}</loc>
        <lastmod>{{ $service->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    @endforeach

    {{-- Page builder pages --}}
    @foreach ($pages as $page)
    <url>
        <loc>{{ route('page.show', $page->slug) }}</loc>
        <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

</urlset>
