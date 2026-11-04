@extends('admin.master_page')
@section('title', 'Booking settings | Forward Edge')
@php $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun']; @endphp

@section('main')
<div class="fe-page">
    <div class="pagetitle">
        <div>
            <h1>Availability &amp; calendar</h1>
            <p class="fe-title-sub">When visitors can book, and who is told.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Back to bookings</a>
    </div>

    <form method="POST" action="{{ route('admin.bookings.settings.update') }}" data-unsaved>
        @csrf @method('PUT')
        <section class="fe-panel mb-3">
            <h2 class="fe-h3">Who gets the requests</h2>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="s-name">Consultant name</label><input id="s-name" name="consultant_name" class="form-control" value="{{ old('consultant_name', $s['consultant_name']) }}" required></div>
                <div class="col-md-6"><label class="form-label" for="s-email">Consultant email</label><input id="s-email" type="email" name="consultant_email" class="form-control" value="{{ old('consultant_email', $s['consultant_email']) }}" required>
                    <div class="form-text">Gets every request with Approve and Decline buttons, and the calendar invite once approved.</div></div>
                <div class="col-12"><label class="form-label" for="s-cc">Copy to (optional)</label><input id="s-cc" name="cc_emails" class="form-control" value="{{ old('cc_emails', $s['cc_emails']) }}" placeholder="team@example.com, another@example.com"><div class="form-text">Separate several addresses with commas.</div></div>
            </div>
        </section>

        <section class="fe-panel mb-3">
            <h2 class="fe-h3">Working hours</h2>
            <div class="mb-3">
                <p class="form-label mb-1">Days</p>
                @foreach ($days as $n => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="d{{ $n }}" name="days[]" value="{{ $n }}" @checked(in_array($n, old('days', $s['days'])))>
                        <label class="form-check-label" for="d{{ $n }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            <div class="row g-3">
                <div class="col-6 col-md-3"><label class="form-label" for="s-start">From</label><input id="s-start" type="time" name="start_time" class="form-control" value="{{ old('start_time', $s['start_time']) }}" required></div>
                <div class="col-6 col-md-3"><label class="form-label" for="s-end">Until</label><input id="s-end" type="time" name="end_time" class="form-control" value="{{ old('end_time', $s['end_time']) }}" required></div>
                <div class="col-md-6"><label class="form-label" for="s-tz">Time zone</label><input id="s-tz" name="timezone" class="form-control" value="{{ old('timezone', $s['timezone']) }}" required><div class="form-text">For example Africa/Lagos. Visitors see times in this zone.</div></div>
                <div class="col-6 col-md-3"><label class="form-label" for="s-len">Call length (minutes)</label><input id="s-len" type="number" name="slot_minutes" class="form-control" min="10" max="180" value="{{ old('slot_minutes', $s['slot_minutes']) }}" required></div>
                <div class="col-6 col-md-3"><label class="form-label" for="s-buf">Gap after each call</label><input id="s-buf" type="number" name="buffer_minutes" class="form-control" min="0" max="120" value="{{ old('buffer_minutes', $s['buffer_minutes']) }}" required></div>
                <div class="col-6 col-md-3"><label class="form-label" for="s-notice">Minimum notice (hours)</label><input id="s-notice" type="number" name="min_notice_hours" class="form-control" min="0" value="{{ old('min_notice_hours', $s['min_notice_hours']) }}" required></div>
                <div class="col-6 col-md-3"><label class="form-label" for="s-ahead">Bookable up to (days)</label><input id="s-ahead" type="number" name="max_days_ahead" class="form-control" min="1" max="180" value="{{ old('max_days_ahead', $s['max_days_ahead']) }}" required></div>
                <div class="col-12"><label class="form-label" for="s-block">Days off</label><textarea id="s-block" name="blocked_dates" class="form-control" rows="2" placeholder="2026-12-25, 2026-12-26">{{ old('blocked_dates', implode(', ', $s['blocked_dates'])) }}</textarea><div class="form-text">Dates as YYYY-MM-DD, separated by commas. No slots are offered on these days.</div></div>
            </div>
        </section>

        <button class="btn btn-primary">Save settings</button>
    </form>

    <section class="fe-panel mt-4">
        <h2 class="fe-h3">Put confirmed calls on a calendar automatically</h2>
        <p class="fe-item-s" style="white-space:normal">Every approved call is emailed to the consultant as an invite. To also see them all in one place, subscribe once to this private link in Google Calendar (Other calendars, From URL), Outlook or Apple Calendar. Calendar apps refresh subscriptions every few hours, so a new call can take a while to appear. Anyone with the link can see the calls, so keep it private.</p>
        <input class="form-control fe-num mb-2" readonly value="{{ $feedUrl }}" onclick="this.select()" aria-label="Calendar feed link">
        <form method="POST" action="{{ route('admin.bookings.feed.regenerate') }}" data-confirm="The current link will stop working. You will need to subscribe again with the new one." data-confirm-title="Create a new link?" data-confirm-action="Create new link">
            @csrf
            <button class="btn btn-sm btn-outline-secondary">Create a new link</button>
        </form>
    </section>
</div>
@endsection
