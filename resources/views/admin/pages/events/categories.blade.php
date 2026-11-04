@extends('admin.master_page')
@section('title', 'Event categories | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>Event categories</h1>
            <p class="fe-title-sub">Bootcamps, academic programmes, workshops and anything else you run. Visitors can filter the events page by these.</p>
        </div>
        <a href="{{ route('admin.events.list') }}" class="btn btn-outline-secondary">Back to events</a>
    </div>

    <form method="POST" action="{{ route('admin.event-categories.store') }}" class="fe-filters mb-4">
        @csrf
        <div class="fe-filters-grow">
            <label class="form-label" for="cat-new">New category</label>
            <input id="cat-new" name="name" class="form-control" maxlength="80" placeholder="For example: Academic programme" required>
        </div>
        <div class="fe-filters-actions"><button class="btn btn-primary">Add category</button></div>
    </form>
    @error('name')<p class="fe-error mb-3">{{ $message }}</p>@enderror

    <div class="card">
        <div class="card-body p-0">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col" style="width:110px">Order</th>
                        <th scope="col" class="text-end">Events</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $c)
                        <tr>
                            <td colspan="2">
                                <form method="POST" action="{{ route('admin.event-categories.update', $c) }}" class="d-flex flex-wrap gap-2 align-items-center">
                                    @csrf @method('PUT')
                                    <label class="visually-hidden" for="cn-{{ $c->id }}">Name</label>
                                    <input id="cn-{{ $c->id }}" name="name" value="{{ $c->name }}" class="form-control" style="max-width:320px" maxlength="80" required>
                                    <label class="visually-hidden" for="co-{{ $c->id }}">Order</label>
                                    <input id="co-{{ $c->id }}" name="sort_order" type="number" min="0" value="{{ $c->sort_order }}" class="form-control fe-num" style="width:90px">
                                    <button class="btn btn-sm btn-outline-secondary">Save</button>
                                </form>
                            </td>
                            <td class="text-end fe-num">{{ $c->events_count }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.event-categories.destroy', $c) }}" class="d-inline"
                                      data-confirm="“{{ $c->name }}” will be removed. Its {{ $c->events_count }} {{ Str::plural('event', $c->events_count) }} will become uncategorised, not deleted."
                                      data-confirm-title="Delete this category?" data-confirm-action="Delete category">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
