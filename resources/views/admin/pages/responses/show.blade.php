@extends('admin.master_page')
@section('title', 'Response | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>{{ $response->name ?: ($response->email ?: 'Response') }}</h1>
            <p class="fe-title-sub">{{ $response->form->name ?? 'Deleted form' }}, received {{ $response->created_at->format('j M Y, g:ia') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.responses.index') }}" class="btn btn-outline-secondary">Back to responses</a>
            <form method="POST" action="{{ route('admin.responses.destroy', $response) }}"
                  data-confirm="This response from {{ $response->name ?: ($response->email ?: 'this contact') }} will be permanently deleted."
                  data-confirm-title="Delete this response?" data-confirm-action="Delete response">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="fe-dash">
        <section class="fe-panel fe-next">
            <p class="fe-label-sm">Answers</p>
            <dl class="fe-answers fe-answers-user">
                @foreach ($response->data as $row)
                    <div>
                        <dt>{{ $row['label'] }}</dt>
                        <dd>{{ is_array($row['value']) ? implode(', ', $row['value']) : $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="fe-panel fe-recent">
            <p class="fe-label-sm">Details</p>
            <dl class="fe-answers">
                <div>
                    <dt>Came from</dt>
                    <dd>
                        {{ $response->source_label ?: 'Unknown' }}
                        @if ($response->source_url)<div class="fe-item-s fe-num">{{ $response->source_url }}</div>@endif
                    </dd>
                </div>
                <div>
                    <dt>Marketing consent</dt>
                    <dd>
                        @if ($response->consent_given)
                            Given {{ $response->consented_at?->format('j M Y, g:ia') }}
                            <div class="fe-item-s" style="white-space:normal">“{{ $response->consent_text }}”</div>
                        @else
                            <span class="badge bg-warning">None recorded</span>
                            <div class="fe-item-s">This contact is left out of the Brevo export.</div>
                        @endif
                    </dd>
                </div>
                @if ($response->form && $response->form->hasResource())
                    <div>
                        <dt>Resource</dt>
                        <dd>
                            {{ $response->form->setting('resource_title') ?: 'Resource' }}:
                            @if ($response->resource_sent_at)
                                emailed {{ $response->resource_sent_at->format('j M Y, g:ia') }}
                            @else
                                <span class="badge bg-warning">Not delivered</span>
                                @if ($response->resource_error)<div class="fe-item-s" style="white-space:normal">{{ $response->resource_error }}</div>@endif
                            @endif
                            <div class="fe-item-s">{{ $response->resource_downloaded_at ? 'Opened ' . $response->download_count . ' ' . Str::plural('time', $response->download_count) . ', first on ' . $response->resource_downloaded_at->format('j M Y, g:ia') : 'Not downloaded yet' }}</div>
                            @if ($response->email)
                                <form method="POST" action="{{ route('admin.responses.resend', $response) }}" class="mt-2">@csrf
                                    <button class="btn btn-sm btn-outline-secondary">{{ $response->resource_sent_at ? 'Send again' : 'Try sending again' }}</button>
                                </form>
                            @endif
                        </dd>
                    </div>
                @endif
                <div>
                    <dt>Brevo export</dt>
                    <dd>{{ $response->exported_at ? 'Exported ' . $response->exported_at->format('j M Y, g:ia') : 'Not exported yet' }}</dd>
                </div>
            </dl>

            @if ($response->email)
                <form method="POST" action="{{ route('admin.responses.forget') }}" class="mt-3"
                      data-confirm="All {{ $sameEmail }} {{ Str::plural('response', $sameEmail) }} from {{ $response->email }}, across every form, will be permanently deleted. Use this for a data-removal request."
                      data-confirm-title="Delete everything about this person?" data-confirm-action="Delete everything">
                    @csrf
                    <input type="hidden" name="email" value="{{ $response->email }}">
                    <button class="btn btn-sm btn-outline-danger">Delete everything for this email</button>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
