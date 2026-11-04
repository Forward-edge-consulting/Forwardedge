@extends('admin.master_page')

@section('title', $service->title . ' - Service Details')



@section('main')
    <div class="container py-5">
        <div class="pagetitle">
            <div>
                <h1>{{ $service->title }}</h1>
                <p class="fe-title-sub">Content blocks shown on this service page, in order.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">Back to services</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContentModal">Add content block</button>
            </div>
        </div>

        {{-- Content List --}}
        <div class="row g-4">
            @forelse($service->contents->sortBy('position') as $content)
                <div class="col-12">
                    <div class="card content-card p-4 shadow-sm">
                        {{-- Added flex-wrap to ensure buttons stack on mobile --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-start">
                            <div class="d-flex align-items-start flex-grow-1 me-0 me-sm-3 mb-2 mb-sm-0">
                                <span class="badge bg-primary me-3 p-2 fw-bold rounded-pill"
                                    style="min-width: 40px;">{{ $content->position }}</span>
                                <div class="w-100">
                                    <span class="content-type-badge bg-light text-secondary border">
                                        {{ ucfirst($content->type) }}
                                    </span>
                                    @if ($content->type === 'heading')
                                    <h5 class="mt-1 mb-2 fw-semibold">
                                        {{ \Illuminate\Support\Str::limit($content->asString(), 80) }}
                                    </h5>
                                    @endif

                                    @if ($content->type === 'image')
                                        <img src="{{ asset('storage/' . $content->content) }}"
                                            alt="Content Image" style="max-height: 80px;" class="rounded shadow-sm">
                                    @elseif ($content->type === 'list')
                                        {{-- 💡 FIX: Use asArray() for iterating lists --}}
                                        <ul class="list-unstyled small text-muted mb-0">
                                            @foreach ($content->asArray() as $item)
                                                <li><i class="bi bi-check-circle-fill text-success me-1"></i>
                                                    {{ \Illuminate\Support\Str::limit($item, 50) }}</li>
                                            @endforeach
                                        </ul>
                                    @elseif (in_array($content->type, ['features', 'feature']))
                                        @php
                                            $feature = $content->asArray();
                                            $isAssoc = is_array($feature) && array_keys($feature) !== range(0, count($feature) - 1);
                                        @endphp
                                        @if ($isAssoc)
                                            <div class="border rounded-3 p-3 bg-light">
                                                <h6 class="mb-1">{{ trim(($feature['number'] ?? '') . ' ' . ($feature['title'] ?? $feature['heading'] ?? 'Untitled feature')) }}</h6>
                                                <p class="text-muted small mb-0">{{ $feature['description'] ?? $feature['paragraph'] ?? '' }}</p>
                                            </div>
                                        @else
                                            <ul class="list-unstyled small text-muted mb-0">
                                                @foreach ($feature as $item)
                                                    <li><i class="bi bi-check-circle-fill text-success me-1"></i>
                                                        {{ \Illuminate\Support\Str::limit($item, 50) }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @elseif ($content->type !== 'heading')
                                        <p class="text-muted small mb-0">
                                            {{ \Illuminate\Support\Str::limit($content->asString(), 220) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="btn-group mt-2 mt-sm-0 flex-shrink-0 ms-auto">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" aria-label="Edit block {{ $content->position }}" title="Edit"
                                    data-bs-target="#editContentModal{{ $content->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form data-confirm="Block {{ $content->position }} will be removed from this service page." data-confirm-title="Delete this content block?" data-confirm-action="Delete" action="{{ route('admin.services.contents.destroy', [$service->id, $content->id]) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Edit Content Modal (Responsive column classes are already good) --}}
                <div class="modal fade" id="editContentModal{{ $content->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form
                                action="{{ route('admin.services.contents.update', [$service->id, $content->id]) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit {{ ucfirst($content->type) }} Block (Pos:
                                        {{ $content->position }})</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Position</label>
                                        <input type="number" name="position" value="{{ $content->position }}"
                                            class="form-control" required min="1">
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <label class="form-label">Type (Locked)</label>
                                        <input type="text" class="form-control"
                                            value="{{ ucfirst($content->type) }}" disabled>
                                    </div>

                                    @if ($content->type === 'image' || $content->type === 'video')
                                        <div class="col-12">
                                            <label class="form-label">Replace File</label>
                                            {{-- Content is a file path (string) here, no need for helper --}}
                                            <input type="file" name="content" class="form-control"
                                                accept="{{ $content->type === 'image' ? 'image/*' : 'video/*' }}">
                                            <div class="form-text">Current: {{ basename($content->content) }}</div>
                                        </div>
                                    @elseif ($content->type === 'list')
                                        <div class="col-12">
                                            <label class="form-label">{{ ucfirst($content->type) }} Items (One per
                                                line)</label>
                                            {{-- 💡 FIX: Use asArray() to get the list items and implode them for the textarea --}}
                                            <textarea name="content" class="form-control" rows="6" required>{{ implode("\n", $content->asArray()) }}</textarea>
                                            <div class="form-text">Each line will be saved as a separate item.</div>
                                        </div>
                                    @elseif (in_array($content->type, ['features', 'feature']))
                                        @php
                                            $feature = $content->asArray();
                                            $feature = is_array($feature) ? $feature : [];
                                        @endphp
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Feature Heading</label>
                                            <input type="text" name="content[heading]" class="form-control"
                                                value="{{ $feature['heading'] ?? '' }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Feature Description</label>
                                            <textarea name="content[paragraph]" class="form-control" rows="4" required>{{ $feature['paragraph'] ?? '' }}</textarea>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <label class="form-label">Content</label>
                                            {{-- 💡 FIX: Use asString() to safely display the string content in the textarea --}}
                                            <textarea name="content" class="form-control" rows="5" required>{{ $content->asString() }}</textarea>
                                        </div>
                                    @endif

                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-5 text-center text-muted border-0 shadow-sm">
                        <i class="bi bi-bricks fs-1 mb-3 text-secondary"></i>
                        <p class="fs-5 mb-0">No content blocks defined for this service yet.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>


    {{-- Add Content Modal (Kept as is for structure reference) --}}
    <div class="modal fade" id="addContentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.services.contents.store', $service->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Content Block</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Block Type</label>
                            <select name="type" id="addBlockType" class="form-select" required>
                                <option value="" disabled selected>Select content type...</option>
                                <option value="heading">Heading</option>
                                <option value="paragraph">Paragraph/Text</option>
                                <option value="list">List</option>
                                <option value="features">Features/Benefits List</option>
                                <option value="image">Image</option>
                                <option value="video">Video URL</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control" min="1"
                                value="{{ $service->contents->max('position') + 1 }}">
                        </div>

                        <!-- Dynamic Fields -->
                        <div class="col-12" id="addDynamicContent">
                            <p class="text-muted small">Select a block type above.</p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Block</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // --- Dynamic Content Input for ADD Modal ---
                const typeSelect = document.getElementById('addBlockType');
                const dynamicFieldContainer = document.getElementById('addDynamicContent');

                const updateDynamicFields = (type) => {
                    dynamicFieldContainer.innerHTML = ''; // Clear previous content

                    let contentHtml = '';

                    switch (type) {
                        case 'heading':
                        case 'paragraph':
                            contentHtml = `
                                <label class="form-label">${type === 'heading' ? 'Heading Text' : 'Paragraph Text'}</label>
                                <textarea name="content" class="form-control" rows="${type === 'heading' ? '1' : '5'}" required placeholder="Enter the text content"></textarea>`;
                            break;
                        case 'list':
                            contentHtml = `
                                <label class="form-label">${type === 'list' ? 'List Items' : 'Features (one per line)'}</label>
                                <textarea name="content" class="form-control" rows="6" placeholder="Item 1\nItem 2\nItem 3" required></textarea>
                                <div class="form-text">Each line will be saved as a separate item.</div>`;
                            break;
                        case 'features':
                            contentHtml = `
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Feature Heading</label>
                                        <input type="text" name="content[heading]" class="form-control" placeholder="Feature title" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Feature Description</label>
                                        <textarea name="content[paragraph]" class="form-control" rows="4" placeholder="Describe this benefit" required></textarea>
                                    </div>
                                </div>`;
                            break;
                        case 'image':
                            contentHtml = `
                                <label class="form-label">Upload Image File</label>
                                <input type="file" name="content" class="form-control" accept="image/*" required>`;
                            break;
                        case 'video':
                            contentHtml = `
                                <label class="form-label">Video URL (e.g., YouTube or Vimeo link)</label>
                                <input type="url" name="content" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>`;
                            break;
                        default:
                            contentHtml = `<p class="text-danger small">Select a block type to see the corresponding input fields.</p>`;
                            break;
                    }

                    dynamicFieldContainer.innerHTML = contentHtml;
                };

                if (typeSelect) {
                    typeSelect.addEventListener('change', function() {
                        updateDynamicFields(this.value);
                    });
                }


                // --- List Item Management (for modals containing list/feature content) ---
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.addEventListener('click', function(e) {
                        if (e.target.closest('.addListItem')) {
                            const container = e.target.closest('.list-group');
                            let newItem = document.createElement("div");
                            newItem.classList.add("input-group", "mb-2");
                            newItem.innerHTML =
                                `
                <input type="text" name="content[]" class="form-control" placeholder="List item" required>
                <button type="button" class="btn btn-outline-danger removeListItem"><i class="bi bi-dash"></i></button>`;
                            container.appendChild(newItem);
                        }
                        if (e.target.closest('.removeListItem')) {
                            e.target.closest('.input-group').remove();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
