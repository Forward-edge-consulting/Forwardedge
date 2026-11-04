@extends('admin.master_page')
@section('title', 'Booking | Forward Edge')

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>{{ $booking->name ?: $booking->email }}</h1>
            <p class="fe-title-sub">{{ $booking->whenLabel() }}</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Back to bookings</a>
    </div>

    <div class="fe-dash">
        <section class="fe-panel fe-next">
            <p class="fe-label-sm">Details</p>
            <dl class="fe-answers fe-answers-user">
                <div><dt>Status</dt><dd class="fe-dd-plain">
                    @switch($booking->status)
                        @case('pending') <span class="badge bg-warning">Needs approval</span> @break
                        @case('approved') <span class="badge bg-success">Confirmed</span> @break
                        @case('declined') <span class="badge bg-secondary">Declined</span> @break
                        @default <span class="badge bg-secondary">Cancelled</span>
                    @endswitch
                    @if ($booking->decided_at)<div class="fe-item-s">{{ ucfirst($booking->status) }} {{ $booking->decided_at->format('j M Y, g:ia') }}{{ $booking->decided_via ? ' via ' . $booking->decided_via : '' }}</div>@endif
                </dd></div>
                @foreach ($booking->details ?? [] as $row)
                    <div><dt>{{ $row['label'] }}</dt><dd>{{ is_array($row['value']) ? implode(', ', $row['value']) : $row['value'] }}</dd></div>
                @endforeach
                @if ($booking->meeting_link)<div><dt>Meeting link</dt><dd><a href="{{ $booking->meeting_link }}" target="_blank" rel="noopener">{{ $booking->meeting_link }}</a></dd></div>@endif
                @if ($booking->note_to_visitor)<div><dt>Message sent</dt><dd>{{ $booking->note_to_visitor }}</dd></div>@endif
                @if ($booking->decline_reason)<div><dt>Reason given</dt><dd>{{ $booking->decline_reason }}</dd></div>@endif
            </dl>
            @if ($booking->submission)
                <a href="{{ route('admin.responses.show', $booking->submission) }}" class="btn btn-sm btn-outline-secondary mt-2">Open the response</a>
            @endif
        </section>

        <section class="fe-panel fe-recent">
            @if ($booking->isPending())
                <p class="fe-label-sm">Approve</p>
                <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}" class="mb-4">
                    @csrf
                    <label class="form-label" for="b-link">Meeting link (optional)</label>
                    <input id="b-link" type="url" name="meeting_link" class="form-control mb-2" placeholder="Google Meet, Zoom or Teams link" value="{{ old('meeting_link', \App\Models\BookingSettings::current()->get('meeting_link')) }}">
                    <label class="form-label" for="b-note">Message to the visitor (optional)</label>
                    <textarea id="b-note" name="note" class="form-control mb-2" rows="2" maxlength="1000"></textarea>
                    <button class="btn btn-primary">Approve and send invites</button>
                </form>
                <p class="fe-label-sm">Decline</p>
                <form method="POST" action="{{ route('admin.bookings.decline', $booking) }}" class="mb-3"
                      data-confirm="{{ $booking->name ?: 'The visitor' }} will be told the call cannot happen at this time, and the slot will be free again."
                      data-confirm-title="Decline this request?" data-confirm-action="Decline">
                    @csrf
                    <textarea name="reason" class="form-control mb-2" rows="2" maxlength="1000" placeholder="Reason (optional, sent to the visitor)"></textarea>
                    <button class="btn btn-outline-danger">Decline request</button>
                </form>
            @elseif ($booking->isApproved())
                <p class="fe-label-sm">Calendar</p>
                <p class="fe-item-s" style="white-space:normal">Invites were emailed to the visitor and to the consultant. This booking also appears in the calendar feed.</p>
                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}"
                      data-confirm="The visitor will be emailed that this call is cancelled and the time will be free again."
                      data-confirm-title="Cancel this booking?" data-confirm-action="Cancel booking">
                    @csrf
                    <button class="btn btn-outline-danger">Cancel booking</button>
                </form>
            @endif

            <p class="fe-label-sm mt-4">Private notes</p>
            <form method="POST" action="{{ route('admin.bookings.notes', $booking) }}">
                @csrf @method('PUT')
                <textarea name="internal_notes" class="form-control mb-2" rows="3" maxlength="3000">{{ old('internal_notes', $booking->internal_notes) }}</textarea>
                <button class="btn btn-sm btn-outline-secondary">Save notes</button>
            </form>
        </section>
    </div>
</div>
@endsection
