@extends('admin.master_page')
@php
    use App\Support\FormFields;
    $isNew = !$form->exists;
    $s = array_merge(\App\Models\Form::defaultSettings(), $form->settings ?? []);
@endphp
@section('title', ($isNew ? 'New form' : $form->name) . ' | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>{{ $isNew ? 'New form' : $form->name }}</h1>
            @unless ($isNew || $form->is_system)
                <p class="fe-title-sub">Share link: <a href="{{ $form->public_url }}" target="_blank" rel="noopener" class="fe-num">{{ $form->public_url }}</a></p>
            @endunless
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary">Back to forms</a>
            @unless ($isNew)
                <a href="{{ route('admin.responses.index', ['form' => $form->id]) }}" class="btn btn-outline-secondary">Responses</a>
            @endunless
        </div>
    </div>

    <form method="POST" id="formBuilder" data-unsaved enctype="multipart/form-data"
          action="{{ $isNew ? route('admin.forms.store') : route('admin.forms.update', $form) }}">
        @csrf
        @unless ($isNew) @method('PUT') @endunless
        <input type="hidden" name="fields_json" id="fieldsJson" value="{{ json_encode($form->fields ?? []) }}">

        <div class="fb-grid">
            <div class="fb-main">
                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">Details</h2>
                    <div class="mb-3">
                        <label class="form-label" for="fb-name">Form name</label>
                        <input id="fb-name" name="name" class="form-control" value="{{ old('name', $form->name) }}" required maxlength="120" placeholder="For example: Speaker application">
                    </div>
                    @unless ($form->is_system)
                        <div class="mb-3">
                            <label class="form-label" for="fb-slug">Link ending</label>
                            <div class="input-group">
                                <span class="input-group-text">/f/</span>
                                <input id="fb-slug" name="slug" class="form-control" value="{{ old('slug', $form->slug) }}" maxlength="120" placeholder="speaker-application">
                            </div>
                            <div class="form-text">Leave blank to generate it from the name.</div>
                        </div>
                    @endunless
                    <div>
                        <label class="form-label" for="fb-desc">Short intro (shown above the form)</label>
                        <textarea id="fb-desc" name="description" class="form-control" rows="2" maxlength="500">{{ old('description', $form->description) }}</textarea>
                    </div>
                </section>

                <section class="fe-panel fb-section">
                    <div class="fe-panel-head">
                        <h2 class="fe-h3">Fields</h2>
                    </div>
                    @if ($form->is_system)
                        <p class="fe-empty">The fields of this built-in form are fixed because the website footer and blog use them. You can still change the settings below.</p>
                        <ul class="fb-fixed">
                            @foreach ($form->fields as $f)
                                <li>{{ $f['label'] }} <span class="fe-item-s">{{ FormFields::TYPES[$f['type']] ?? $f['type'] }}{{ $f['required'] ? ', required' : '' }}</span></li>
                            @endforeach
                            @if (empty($form->fields))
                                <li class="fe-item-s">Fields come from each page-builder form block.</li>
                            @endif
                        </ul>
                    @else
                        <div id="fbFields" class="fb-fields" aria-live="polite"></div>
                        <p class="fe-label-sm mt-3 mb-2">Add a field</p>
                        <div class="fb-types" role="group" aria-label="Add a field">
                            @foreach (FormFields::TYPES as $type => $label)
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-add="{{ $type }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">After someone submits</h2>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label" for="fb-submit">Button label</label>
                            <input id="fb-submit" name="submit_label" class="form-control" value="{{ old('submit_label', $s['submit_label']) }}" maxlength="40">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="fb-success">Thank-you message</label>
                            <textarea id="fb-success" name="success_message" class="form-control" rows="2" maxlength="300">{{ old('success_message', $s['success_message']) }}</textarea>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="fb-redirect">Go to this page afterwards</label>
                            <input id="fb-redirect" name="redirect_url" class="form-control" value="{{ old('redirect_url', $s['redirect_url']) }}" maxlength="190" placeholder="/thank-you">
                            <div class="form-text">Optional. A page on this site, starting with /. Leave empty to stay on the page and show the thank-you message. Forms that give a download stay on the page.</div>
                        </div>
                    </div>
                </section>

                <style>#fb-resource [data-res][hidden]{display:none!important}</style>
                <section class="fe-panel fb-section" id="fb-resource">
                    <h2 class="fe-h3">Send a resource after submit</h2>
                    <p class="fe-item-s mb-3" style="white-space:normal">Give something back for the signup, such as a PDF guide. The person sees a popup saying it is in their email, and gets the download link by email. Their details are saved first, so nothing is lost if the email fails.</p>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="fb-res-on" name="resource_enabled" value="1" @checked(old('resource_enabled', $s['resource_enabled']))>
                        <label class="form-check-label" for="fb-res-on">Send a resource</label>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="fb-res-title">Resource name</label>
                            <input id="fb-res-title" name="resource_title" class="form-control" value="{{ old('resource_title', $s['resource_title']) }}" maxlength="120" placeholder="Small business security checklist">
                            <div class="form-text">Shown on the download button.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="fb-res-kind">Type</label>
                            <select id="fb-res-kind" name="resource_kind" class="form-select">
                                <option value="file" @selected(old('resource_kind', $s['resource_kind']) === 'file')>PDF file</option>
                                <option value="link" @selected(old('resource_kind', $s['resource_kind']) === 'link')>Link to a page or file</option>
                            </select>
                        </div>
                        <div class="col-md-6" data-res="file">
                            <label class="form-label" for="fb-res-file">PDF (up to 20 MB)</label>
                            <input id="fb-res-file" type="file" name="resource_file" accept="application/pdf" class="form-control">
                            @if ($s['resource_path'])
                                <div class="form-text">Current file: <strong>{{ $s['resource_filename'] ?: basename($s['resource_path']) }}</strong>. Choosing another replaces it.</div>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="fb-res-rm" name="remove_resource_file" value="1">
                                    <label class="form-check-label" for="fb-res-rm">Remove the current file</label>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6" data-res="link">
                            <label class="form-label" for="fb-res-url">Link</label>
                            <input id="fb-res-url" type="url" name="resource_url" class="form-control" value="{{ old('resource_url', $s['resource_url']) }}" maxlength="500" placeholder="https://">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="fb-res-subject">Email subject</label>
                            <input id="fb-res-subject" name="resource_email_subject" class="form-control" value="{{ old('resource_email_subject', $s['resource_email_subject']) }}" maxlength="150">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="fb-res-body">Email message</label>
                            <textarea id="fb-res-body" name="resource_email_body" class="form-control" rows="3" maxlength="2000">{{ old('resource_email_body', $s['resource_email_body']) }}</textarea>
                            <div class="form-text">The download button is added under this message. This replaces the confirmation email above.</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="fb-pop-title">Popup title</label>
                            <input id="fb-pop-title" name="popup_title" class="form-control" value="{{ old('popup_title', $s['popup_title']) }}" maxlength="100">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="fb-pop-msg">Popup message</label>
                            <textarea id="fb-pop-msg" name="popup_message" class="form-control" rows="2" maxlength="400">{{ old('popup_message', $s['popup_message']) }}</textarea>
                            <div class="form-text">Use {email} to show the address it was sent to.</div>
                        </div>
                    </div>
                </section>

                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">Consent for emails (Brevo)</h2>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="fb-consent" name="consent_required" value="1" @checked(old('consent_required', $s['consent_required']))>
                        <label class="form-check-label" for="fb-consent">Ask people to agree to be emailed</label>
                    </div>
                    <label class="form-label" for="fb-consent-text">Wording next to the tick box</label>
                    <textarea id="fb-consent-text" name="consent_text" class="form-control" rows="2" maxlength="500">{{ old('consent_text', $s['consent_text']) }}</textarea>
                    <div class="form-text">Only people who tick this are included when you export contacts for Brevo. The wording and time are saved with each response.</div>
                </section>

                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">Emails</h2>
                    <div class="mb-3">
                        <label class="form-label" for="fb-notify">Email me when someone responds</label>
                        <input id="fb-notify" type="email" name="notify_email" class="form-control" value="{{ old('notify_email', $s['notify_email']) }}" placeholder="{{ config('contact.email') }}">
                        <div class="form-text">Leave empty to use {{ config('contact.email') }}. Responses are always saved here first.</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="fb-reply" name="auto_reply" value="1" @checked(old('auto_reply', $s['auto_reply']))>
                        <label class="form-check-label" for="fb-reply">Send the person a confirmation email</label>
                    </div>
                    <div class="form-text mb-0">The confirmation email is sent automatically with our standard wording.</div>
                </section>

                <section class="fe-panel fb-section">
                    <h2 class="fe-h3">Availability</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="fb-status">Status</label>
                            <select id="fb-status" name="status" class="form-select">
                                <option value="open" @selected(old('status', $form->status) === 'open')>Open</option>
                                <option value="closed" @selected(old('status', $form->status) === 'closed')>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="fb-closes">Close automatically on</label>
                            <input id="fb-closes" type="datetime-local" name="closes_at" class="form-control" value="{{ old('closes_at', $form->closes_at?->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                </section>

                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif

                <div class="d-flex flex-wrap gap-2 align-items-center fb-save">
                    <button type="submit" class="btn btn-primary">{{ $isNew ? 'Create form' : 'Save form' }}</button>
                    @unless ($isNew || $form->is_system)
                        <a href="{{ $form->public_url }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">View form</a>
                    @endunless
                </div>
            </div>

            <aside class="fb-preview" aria-label="Preview">
                <p class="fe-label-sm">Preview</p>
                <div class="fb-preview-card" id="fbPreview"></div>
            </aside>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    window.FE_FORM = {
        fields: @json($form->fields ?? []),
        types: @json(FormFields::TYPES),
        maps: @json(FormFields::MAPS),
        choice: @json(FormFields::CHOICE_TYPES),
        locked: @json((bool) $form->is_system),
        submitLabel: @json($s['submit_label']),
    };
</script>
<script src="{{ asset('backend/assets/js/fe-form-builder.js') }}?v=2"></script>
<script>
(function () {
    var kind = document.getElementById('fb-res-kind');
    if (!kind) return;
    var sync = function () {
        document.querySelectorAll('#fb-resource [data-res]').forEach(function (el) { el.hidden = el.dataset.res !== kind.value; });
    };
    kind.addEventListener('change', sync); sync();
})();
</script>
@endpush
