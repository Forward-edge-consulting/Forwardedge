@extends('admin.master_page')
@section('title', 'Blog | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>Blog</h1>
            <p class="fe-title-sub">Write posts that bring readers in, and put a signup form in front of them.</p>
        </div>
        <form method="POST" action="{{ route('admin.blogs.store') }}">
            @csrf
            <button class="btn btn-primary">New post</button>
        </form>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Post</th>
                        <th scope="col">Status</th>
                        <th scope="col">Category</th>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($blogs as $blog)
                        <tr>
                            <td>
                                <a href="{{ route('admin.blogs.show', $blog->id) }}">{{ $blog->title }}</a>
                                <div class="fe-item-s fe-num">/blog/{{ $blog->slug }}</div>
                            </td>
                            <td>
                                @if ($blog->status === 'published')
                                    <span class="badge bg-success">Published</span>
                                @elseif ($blog->status === 'scheduled')
                                    <span class="badge bg-primary">Scheduled</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </td>
                            <td>{{ $blog->category ?: '—' }}</td>
                            <td class="fe-num text-nowrap">
                                {{ $blog->status === 'scheduled' ? 'Goes live ' . $blog->published_at->format('j M Y, g:ia') : $blog->display_date?->format('j M Y') }}
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.blogs.show', $blog->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('admin.blogs.destroy', $blog->id) }}"
                                          data-confirm="“{{ Str::limit($blog->title, 80) }}” and its {{ $blog->details_count }} content {{ Str::plural('block', $blog->details_count) }} will be permanently deleted."
                                          data-confirm-title="Delete this post?" data-confirm-action="Delete post">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-5"><p class="fe-empty mb-0">No posts yet. Press New post to write your first one.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
