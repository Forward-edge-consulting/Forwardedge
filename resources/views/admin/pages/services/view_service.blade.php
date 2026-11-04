@extends('admin.master_page')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@push('styles')
<style>
    .service-table .service-thumbnail-wrapper img{
        width:100px;
        height:100px;
        object-fit:cover;
    }
    @media (max-width: 575.98px){
        .service-actions{
            flex-direction:column !important;
            width:100%;
        }
        .service-actions .btn{
            width:100%;
        }
        .service-table .service-thumbnail-wrapper img{
            width:70px;
            height:70px;
        }
    }
</style>
@endpush

@section('title', 'Services Management')

@section('main')
<div class="container py-4">
    <div class="pagetitle">
        <div class="pagetitle-left">
            <div class="pagetitle-icon"><i class="bi bi-collection"></i></div>
            <div>
                <h1>Services</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Services</li>
                    </ol>
                </nav>
            </div>
        </div>
        <a href="{{ route('admin.services.add') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i> Add Service
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0 service-table">
                <thead>
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Thumbnail</th>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $index => $service)
                        <tr class="table-row-hover">
                            <td class="fw-semibold py-4 px-4">{{ $index + 1 }}</td>
                            <td class="py-4 px-4">
                                @php $thumb = $service->thumbnail ? Storage::url($service->thumbnail) : null; @endphp
                                @if ($thumb)
                                    <div class="service-thumbnail-wrapper shadow-sm rounded-circle border">
                                        <img src="{{ $thumb }}" alt="{{ $service->title }}" class="img-fluid rounded-circle">
                                    </div>
                                @else
                                    <div class="service-thumbnail-wrapper bg-light shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width:80px;height:80px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-4 fw-bold">{{ $service->title }}</td>
                            <td class="py-4 px-4">{{ Str::limit($service->brief_description, 50) }}</td>
                            <td class="py-4 px-4 text-center">
                                <div class="d-flex gap-2 justify-content-center service-actions flex-sm-row flex-column">
                                <button class="btn btn-sm btn-outline-success rounded-pill px-3 py-2"
                                    data-bs-toggle="modal" data-bs-target="#updateServiceModal"
                                    data-id="{{ $service->id }}"
                                    data-title="{{ htmlentities($service->title, ENT_QUOTES) }}"
                                    data-description="{{ htmlentities($service->brief_description, ENT_QUOTES) }}"
                                    data-thumbnail="{{ $service->thumbnail }}">
                                    <i class="bi bi-pencil"></i> Update
                                </button>
                                <a href="{{ route('admin.services.show', $service->id) }}"
                                    class="btn btn-sm btn-outline-info rounded-pill px-3 py-2">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <form data-confirm="“{{ $service->title }}” and all of its content blocks will be permanently removed." data-confirm-title="Delete this service?" data-confirm-action="Delete" action="{{ route('admin.services.destroy', $service->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted fs-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                🚀 No services yet. Click "Add Service" to get started!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateServiceModal" tabindex="-1" aria-labelledby="updateServiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="updateServiceForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="serviceId">
                    <div class="mb-3 text-left">
                        <div class="modal-thumbnail-wrapper">
                            <img id="currentThumbnail" src="" alt="Thumbnail"  class="img-fluid rounded-circle shadow-sm border mb-2"
                                                style="width: 250px; height: 250px; object-fit: cover;">
                        </div>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="serviceTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Brief Description</label>
                        <textarea name="brief_description" id="serviceDescription" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success rounded-pill px-4">Update Service</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const updateModal = document.getElementById('updateServiceModal');
    updateModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');
        const thumbnail = button.getAttribute('data-thumbnail');

        document.getElementById('serviceId').value = id;
        document.getElementById('serviceTitle').value = title;
        document.getElementById('serviceDescription').value = description;

        const img = document.getElementById('currentThumbnail');
        img.src = thumbnail ? `/storage/${thumbnail}` : '/placeholder-image.png';

        // Optional: update form action dynamically
        document.getElementById('updateServiceForm').action = `/admin/services/${id}`;
    });
</script>
@endsection
