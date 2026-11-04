@extends('user.master_page')
@section('body_attributes', 'data-native-scroll')
@section('title', 'Review discovery call request | Forward Edge Consulting')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/forms.css') }}?v=4">
<style>
.fe-review{padding:56px 0 96px;background:#fff}.fe-review-wrap{max-width:640px;margin:auto}
.fe-review h1{font-size:clamp(28px,3vw,38px);line-height:1.12;letter-spacing:-.025em;color:#0c1e21;margin:0 0 20px}
.fe-review-when{padding:16px 20px;border-radius:14px;background:#fff8e7;margin:0 0 20px;color:#0c1e21}
.fe-review-when strong{display:block;font-size:20px}
.fe-review dl{margin:0 0 28px}.fe-review dl div{display:grid;grid-template-columns:150px 1fr;gap:12px;padding:10px 0;border-top:1px solid #e1e8e8}
.fe-review dt{color:#67787a;font-size:14px}.fe-review dd{margin:0;color:#0c1e21}
.fe-review-box{border:1px solid #c9d1d1;border-radius:16px;padding:22px;margin:0 0 18px}
.fe-review-box h2{font-size:20px;margin:0 0 12px;color:#0c1e21}
.fe-review .fe-form-label{margin-top:10px}.fe-review textarea,.fe-review input[type=url]{display:block;width:100%;padding:11px 14px;font:inherit;border:1px solid #c9d1d1;border-radius:10px}
.fe-review-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:12px 26px;border:0;border-radius:50px;font:inherit;font-weight:700;cursor:pointer;margin-top:14px;background:#FDB714;color:#0c1e21}
.fe-review-btn.is-quiet{background:#fff;border:1px solid #c9d1d1}
.fe-review-state{padding:14px 18px;border-radius:12px;background:#eaf6ee;color:#1b7a4f;font-weight:600;margin:0 0 20px}
.fe-review-state.is-warn{background:#f3f6f6;color:#364e52}
</style>
@endpush
@section('main')
<section class="fe-review"><div class="container"><div class="fe-review-wrap">
    <h1>Discovery call request</h1>

    @if (session('decided') === 'approve')
        <p class="fe-review-state">Approved. {{ $booking->name ?: 'The visitor' }} has been emailed, and a calendar invite has been sent to {{ $settings->get('consultant_name') }}.</p>
    @elseif (session('decided') === 'decline')
        <p class="fe-review-state is-warn">Declined. The time is free again and the visitor has been told.</p>
    @elseif (session('already'))
        <p class="fe-review-state is-warn">This request was already {{ session('already') }}. Nothing was changed.</p>
    @endif

    <p class="fe-review-when"><strong>{{ $when }}</strong>30 minutes, online</p>
    <dl>
        @foreach ($booking->details ?? [] as $row)
            @continue(($row['type'] ?? '') === 'slot')
            <div><dt>{{ $row['label'] }}</dt><dd>{{ is_array($row['value']) ? implode(', ', $row['value']) : $row['value'] }}</dd></div>
        @endforeach
    </dl>

    @if ($booking->isPending())
        <form method="POST" action="{{ $decideUrl }}" class="fe-review-box" data-fe-loader>
            @csrf
            <h2>Approve</h2>
            <label class="fe-form-label" for="r-link">Meeting link (optional)</label>
            <input id="r-link" type="url" name="meeting_link" placeholder="Paste your Google Meet, Zoom or Teams link" maxlength="500" value="{{ old('meeting_link', $settings->get('meeting_link')) }}">
            <label class="fe-form-label" for="r-note">Message to the visitor (optional)</label>
            <textarea id="r-note" name="note" rows="2" maxlength="1000"></textarea>
            <button class="fe-review-btn" name="action" value="approve">Approve and add to calendar</button>
        </form>
        <form method="POST" action="{{ $decideUrl }}" class="fe-review-box" data-fe-loader>
            @csrf
            <h2>Decline</h2>
            <label class="fe-form-label" for="r-reason">Reason (optional, sent to the visitor)</label>
            <textarea id="r-reason" name="reason" rows="2" maxlength="1000" placeholder="For example: I am not available then. Please choose another time."></textarea>
            <button class="fe-review-btn is-quiet" name="action" value="decline">Decline request</button>
        </form>
    @else
        <p class="fe-review-state {{ $booking->isApproved() ? '' : 'is-warn' }}">This request is {{ $booking->status }}.</p>
    @endif
</div></div></section>
@endsection
