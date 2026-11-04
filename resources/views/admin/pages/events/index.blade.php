@extends('admin.master_page')
@section('title', 'Events | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>Events</h1>
            <p class="fe-title-sub">Programmes, workshops and gatherings. Each one can have its own page that tells the story.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.event-categories.index') }}" class="btn btn-outline-secondary">Categories</a>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">New event</a>
        </div>
    </div>

    <form method="GET" class="fe-filters" role="search">
        <div>
            <label class="form-label" for="ev-when">When</label>
            <select id="ev-when" name="when" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="upcoming" @selected($when === 'upcoming')>Upcoming</option>
                <option value="past" @selected($when === 'past')>Past</option>
            </select>
        </div>
        <div>
            <label class="form-label" for="ev-cat">Category</label>
            <select id="ev-cat" name="category" class="form-select form-select-sm">
                <option value="">All categories</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected((int) request('category') === $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="ev-status">Status</label>
            <select id="ev-status" name="status" class="form-select form-select-sm">
                <option value="">Any</option>
                @foreach (['draft' => 'Draft', 'published' => 'Published', 'cancelled' => 'Cancelled'] as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="fe-filters-actions">
            <button class="btn btn-sm btn-outline-secondary">Apply</button>
            @if (request()->hasAny(['when', 'category', 'status']))<a href="{{ route('admin.events.list') }}" class="btn btn-sm btn-ghost">Reset</a>@endif
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Event</th>
                        <th scope="col">Category</th>
                        <th scope="col">Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Page</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        @php $page = $event->pages->sortByDesc('id')->first(); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.events.dashboard', $event) }}">{{ $event->title }}</a>
                                <div class="fe-item-s">{{ $event->location }}</div>
                            </td>
                            <td>{{ $event->category_label }}</td>
                            <td class="fe-num text-nowrap">
                                {{ $event->start_date?->format('j M Y') }}
                                @if ($event->is_past)<span class="fe-item-s d-block">Past</span>@endif
                            </td>
                            <td>
                                @if ($event->status === 'published')<span class="badge bg-success">Published</span>
                                @elseif ($event->status === 'cancelled')<span class="badge bg-danger">Cancelled</span>
                                @else<span class="badge bg-warning">Draft</span>@endif
                            </td>
                            <td>
                                @if ($page)
                                    <a href="{{ route('pb.blocks', $page) }}">{{ $page->status === 'published' ? 'Live' : 'Draft' }}, edit</a>
                                @else
                                    <a href="{{ route('admin.events.dashboard', $event) }}#page" class="text-muted">Not built yet</a>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.events.dashboard', $event) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                                          data-confirm="“{{ Str::limit($event->title, 80) }}” will be deleted.{{ $page ? ' Its page is kept as a draft.' : '' }}"
                                          data-confirm-title="Delete this event?" data-confirm-action="Delete event">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5"><p class="fe-empty mb-0">
                            @if (request()->hasAny(['when', 'category', 'status'])) No events match these filters. @else No events yet. Press New event to add your first one. @endif
                        </p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
