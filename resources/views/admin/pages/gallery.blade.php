@extends('admin.master_page')

@section('title', 'Gallery Management')

@push('styles')
<style>
.gallery-pagination .page-link {
    border: 1px solid #e2e8f0;
    color: #475569;
    background: #fff;
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .82rem;
    font-weight: 500;
    transition: all .18s;
    padding: 0;
}
.gallery-pagination .page-link:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0ea5e9;
}
.gallery-pagination .page-item.active .page-link {
    background: #0ea5e9;
    border-color: #0ea5e9;
    color: #fff;
    box-shadow: 0 2px 8px rgba(14,165,233,.35);
}
.gallery-pagination .page-item.disabled .page-link {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #cbd5e1;
}
</style>
@endpush

@section('main')
<div class="container py-4">
    <div class="pagetitle">
        <div class="pagetitle-left">
            <div class="pagetitle-icon"><i class="bi bi-images"></i></div>
            <div>
                <h1>Gallery</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Gallery</li>
                    </ol>
                </nav>
            </div>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPhotoModal">
            <i class="bi bi-plus me-1"></i> Add Photos
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Upload failed.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div id="galleryUploadAlert" class="alert d-none" role="alert"></div>

    {{-- Category filter --}}
    @if($categories->isNotEmpty())
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.gallery.index') }}"
           class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
            All
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('admin.gallery.index', ['category' => $cat]) }}"
           class="btn btn-sm {{ request('category') === $cat ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
            {{ $cat }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Gallery Grid --}}
    <div class="row g-4">
        @forelse($photos as $photo)
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card gallery-card">
                    <div class="gallery-image-wrapper">
                        <img src="{{ asset('storage/'.$photo->image) }}"
                             class="card-img-top fixed-size-img" alt="{{ $photo->title }}">
                    </div>
                    <div class="card-body px-3 py-3">
                        <h6 class="mb-1 text-truncate">{{ $photo->title ?? 'Untitled' }}</h6>
                        @if($photo->category)
                            <span class="badge bg-info">{{ $photo->category }}</span>
                        @endif
                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-sm btn-outline-secondary flex-fill" data-bs-toggle="modal"
                                data-bs-target="#editPhotoModal{{ $photo->id }}">Edit</button>
                            <button class="btn btn-sm btn-outline-danger flex-fill" data-bs-toggle="modal"
                                data-bs-target="#deletePhotoModal{{ $photo->id }}">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit Modal --}}
            <div class="modal fade" id="editPhotoModal{{ $photo->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form action="{{ route('admin.gallery.update', $photo->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Photo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>Title</label>
                                    <input type="text" name="title" value="{{ $photo->title }}" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Category <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="category" value="{{ $photo->category }}" class="form-control" placeholder="e.g. Bootcamp, Corporate, Community">
                                </div>
                                <div class="mb-3">
                                    <label>Replace Image</label>
                                    <input type="file" name="image" class="form-control">
                                </div>
                                <img src="{{ asset('storage/'.$photo->image) }}" class="img-fluid mt-2 rounded">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Update</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Delete Modal --}}
            <div class="modal fade" id="deletePhotoModal{{ $photo->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form action="{{ route('admin.gallery.destroy', $photo->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Photo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete this photo?
                                <img src="{{ asset('storage/'.$photo->image) }}" class="img-fluid mt-2 rounded">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-images fs-1 d-block mb-3"></i>
                No photos yet.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($photos->hasPages())
    <div class="gallery-pagination mt-5 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
        <p class="text-muted small mb-0">
            Showing <strong>{{ $photos->firstItem() }}</strong>–<strong>{{ $photos->lastItem() }}</strong>
            of <strong>{{ $photos->total() }}</strong> photos
        </p>
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                {{-- Prev --}}
                @if($photos->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link rounded-3"><i class="bi bi-chevron-left"></i></span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link rounded-3" href="{{ $photos->appends(request()->query())->previousPageUrl() }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Page numbers --}}
                @foreach($photos->appends(request()->query())->getUrlRange(1, $photos->lastPage()) as $page => $url)
                    @if($page == $photos->currentPage())
                        <li class="page-item active">
                            <span class="page-link rounded-3">{{ $page }}</span>
                        </li>
                    @elseif(abs($page - $photos->currentPage()) <= 2 || $page == 1 || $page == $photos->lastPage())
                        <li class="page-item">
                            <a class="page-link rounded-3" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @elseif(abs($page - $photos->currentPage()) == 3)
                        <li class="page-item disabled"><span class="page-link rounded-3 border-0 bg-transparent">…</span></li>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($photos->hasMorePages())
                    <li class="page-item">
                        <a class="page-link rounded-3" href="{{ $photos->appends(request()->query())->nextPageUrl() }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link rounded-3"><i class="bi bi-chevron-right"></i></span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>

{{-- Add Photos Modal --}}
<div class="modal fade" id="addPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="galleryUploadForm" action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Title for all photos</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Summer Training" required>
                    </div>
                    <div class="mb-3">
                        <label>Category <small class="text-muted">(optional)</small></label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Bootcamp, Corporate, Community">
                    </div>
                    <div class="mb-3">
                        <label>Choose Photos</label>
                        <input type="file" id="galleryImages" name="images[]" class="form-control" multiple required accept="image/*">
                        <small class="text-muted">Large photos are compressed before upload and sent in small batches.</small>
                    </div>
                    <div class="progress d-none" id="galleryUploadProgress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted d-none mt-2" id="galleryUploadStatus"></small>
                    <div class="alert alert-danger d-none mt-3 mb-0" id="galleryUploadError"></div>
                    <div class="alert alert-success d-none mt-3 mb-0" id="galleryUploadSuccess"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="galleryUploadButton">Upload Photos</button>
                </div>
            </div>
        </form>
    </div>
</div>


{{-- JS to dynamically add inputs --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('galleryUploadForm');
    if (!form) return;

    const batchSize = 3;
    const maxUploadBytes = 1.8 * 1024 * 1024;
    const maxDimension = 1800;
    const fileInput = document.getElementById('galleryImages');
    const button = document.getElementById('galleryUploadButton');
    const progress = document.getElementById('galleryUploadProgress');
    const progressBar = progress?.querySelector('.progress-bar');
    const status = document.getElementById('galleryUploadStatus');
    const errorBox = document.getElementById('galleryUploadError');
    const successBox = document.getElementById('galleryUploadSuccess');

    const setMessage = (box, message) => {
        if (!box) return;
        box.textContent = message;
        box.classList.toggle('d-none', !message);
    };

    const setProgress = (done, total) => {
        const percent = total ? Math.round((done / total) * 100) : 0;
        progress?.classList.remove('d-none');
        if (progressBar) {
            progressBar.style.width = `${percent}%`;
            progressBar.setAttribute('aria-valuenow', String(percent));
        }
        if (status) {
            status.textContent = `Uploaded ${done} of ${total} selected file(s)`;
            status.classList.remove('d-none');
        }
    };

    const loadImage = (file) => new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve(img);
        };
        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error(`Could not read ${file.name}.`));
        };
        img.src = url;
    });

    const canvasToBlob = (canvas, quality) => new Promise((resolve) => {
        canvas.toBlob(resolve, 'image/jpeg', quality);
    });

    async function prepareImage(file) {
        if (file.size <= maxUploadBytes) {
            return file;
        }

        const img = await loadImage(file);
        const scale = Math.min(1, maxDimension / Math.max(img.width, img.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(img.width * scale));
        canvas.height = Math.max(1, Math.round(img.height * scale));
        canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);

        let blob = null;
        for (const quality of [0.82, 0.72, 0.62, 0.52]) {
            blob = await canvasToBlob(canvas, quality);
            if (blob && blob.size <= maxUploadBytes) break;
        }

        if (!blob) {
            throw new Error(`Could not compress ${file.name}.`);
        }

        if (blob.size > maxUploadBytes) {
            throw new Error(`${file.name} is still too large after compression. Please resize it and try again.`);
        }

        const baseName = file.name.replace(/\.[^.]+$/, '') || 'gallery-photo';
        return new File([blob], `${baseName}.jpg`, { type: 'image/jpeg' });
    }

    form.addEventListener('submit', async (event) => {
        const files = Array.from(fileInput?.files || []);
        event.preventDefault();
        setMessage(errorBox, '');
        setMessage(successBox, '');

        button.disabled = true;
        fileInput.disabled = true;

        let uploaded = 0;
        try {
            for (let start = 0; start < files.length; start += batchSize) {
                if (status) {
                    status.textContent = `Preparing ${Math.min(start + batchSize, files.length)} of ${files.length} selected file(s)`;
                    status.classList.remove('d-none');
                }

                const formData = new FormData();
                formData.append('_token', form.querySelector('input[name="_token"]').value);
                formData.append('title', form.querySelector('input[name="title"]').value);

                const preparedFiles = await Promise.all(files.slice(start, start + batchSize).map(prepareImage));
                preparedFiles.forEach((file) => formData.append('images[]', file));

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.success) {
                    const errors = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                    throw new Error(errors || data.message || 'One upload batch failed.');
                }

                uploaded += Number(data.uploaded || 0);
                setProgress(Math.min(start + batchSize, files.length), files.length);
            }

            setMessage(successBox, `${uploaded} photo(s) uploaded successfully. Refreshing gallery...`);
            window.location.reload();
        } catch (error) {
            setMessage(errorBox, error.message || 'Upload failed. Please try again.');
            button.disabled = false;
            fileInput.disabled = false;
        }
    });
});
</script>


@endsection
