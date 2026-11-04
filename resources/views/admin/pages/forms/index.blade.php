@extends('admin.master_page')
@section('title', 'Forms | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>Forms</h1>
            <p class="fe-title-sub">Build a form, share its link or place it on a page. Every response is saved here.</p>
        </div>
        <a href="{{ route('admin.forms.create') }}" class="btn btn-primary">New form</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Form</th>
                        <th scope="col" class="text-end">Responses</th>
                        <th scope="col" class="text-end">Not exported</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($forms as $form)
                        <tr>
                            <td>
                                <a href="{{ route('admin.forms.edit', $form) }}">{{ $form->name }}</a>
                                <div class="fe-item-s">
                                    @if ($form->is_system)
                                        Built in. Used by the website footer, blog and page forms.
                                    @else
                                        <span class="fe-num">/f/{{ $form->slug }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end fe-num">{{ number_format($form->submissions_count) }}</td>
                            <td class="text-end fe-num">{{ number_format($form->new_count) }}</td>
                            <td>
                                @if ($form->isOpen())
                                    <span class="badge bg-success">Open</span>
                                @else
                                    <span class="badge bg-secondary">Closed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                    <a href="{{ route('admin.responses.index', ['form' => $form->id]) }}" class="btn btn-sm btn-outline-secondary">Responses</a>
                                    @unless ($form->is_system)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-copy="{{ $form->public_url }}">Copy link</button>
                                        <form method="POST" action="{{ route('admin.forms.duplicate', $form) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary">Duplicate</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.forms.destroy', $form) }}" class="d-inline"
                                              data-confirm="“{{ $form->name }}” and its {{ number_format($form->submissions_count) }} {{ Str::plural('response', $form->submissions_count) }} will be permanently deleted."
                                              data-confirm-title="Delete this form?" data-confirm-action="Delete form">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy]');
    if (!btn) return;
    var text = btn.dataset.copy;
    var done = function () {
        var old = btn.textContent; btn.textContent = 'Copied';
        setTimeout(function () { btn.textContent = old; }, 1400);
    };
    if (navigator.clipboard) { navigator.clipboard.writeText(text).then(done); }
    else { var i = document.createElement('input'); i.value = text; document.body.appendChild(i); i.select(); document.execCommand('copy'); i.remove(); done(); }
});
</script>
@endpush
