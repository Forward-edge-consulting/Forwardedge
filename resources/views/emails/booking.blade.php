<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Discovery call</title></head>
@php
    $visitor = $audience === 'visitor';
    $first = \Illuminate\Support\Str::before((string) $booking->name, ' ') ?: 'there';
    $btn = 'display:inline-block;padding:14px 26px;border-radius:50px;font-weight:700;text-decoration:none;';
@endphp
<body style="margin:0;padding:24px;background:#f4f7f8;font-family:'Helvetica Neue',Arial,sans-serif;color:#364e52;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr><td align="center">
<table width="580" cellpadding="0" cellspacing="0" role="presentation" style="max-width:100%;background:#ffffff;border-radius:16px;padding:32px;">
    <tr><td style="padding-bottom:20px;"><img src="{{ asset('frontend/assets/images/logos/logo.png') }}" alt="Forward Edge Consulting" width="120" style="display:block;"></td></tr>
    <tr><td style="font-size:16px;line-height:1.65;">

    @if ($kind === 'requested' && $visitor)
        <h1 style="margin:0 0 14px;font-size:24px;color:#0c1e21;">We have your request</h1>
        <p style="margin:0 0 16px;">Hi {{ $first }}, thank you for booking a free security discovery call.</p>
        <p style="margin:0 0 16px;padding:14px 18px;background:#fff8e7;border-radius:12px;"><strong>Requested time:</strong><br>{{ $when }}</p>
        <p style="margin:0 0 16px;">The Forward Edge team will reach out to confirm. Your appointment is confirmed once you receive our confirmation email with the meeting details.</p>

    @elseif ($kind === 'requested')
        <h1 style="margin:0 0 14px;font-size:24px;color:#0c1e21;">New discovery call request</h1>
        <p style="margin:0 0 16px;padding:14px 18px;background:#fff8e7;border-radius:12px;"><strong>{{ $when }}</strong><br>{{ $booking->name }} · {{ $booking->email }}</p>
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 20px;font-size:14px;">
            @foreach ($rows as $row)
                @continue(($row['type'] ?? '') === 'slot')
                <tr><td style="padding:6px 0;border-top:1px solid #e6eef1;width:38%;color:#67787a;">{{ $row['label'] }}</td><td style="padding:6px 0;border-top:1px solid #e6eef1;">{{ is_array($row['value']) ? implode(', ', $row['value']) : $row['value'] }}</td></tr>
            @endforeach
        </table>
        <p style="margin:0 0 8px;">Choose what happens next. You can add your meeting link when you approve.</p>
        <p style="margin:18px 0;"><a href="{{ $reviewUrl }}" style="{{ $btn }}background:#FDB714;color:#0c1e21;">Review and approve or decline</a></p>
        <p style="margin:0;font-size:13px;color:#67787a;">Or manage it in the admin: <a href="{{ $adminUrl }}" style="color:#2c99d4;">{{ $adminUrl }}</a><br>This link works for 14 days.</p>

    @elseif ($kind === 'approved' && $visitor)
        <h1 style="margin:0 0 14px;font-size:24px;color:#0c1e21;">Your discovery call is confirmed</h1>
        <p style="margin:0 0 16px;">Hi {{ $first }}, you are booked in with {{ $consultant }}.</p>
        <p style="margin:0 0 16px;padding:14px 18px;background:#e9f6fd;border-radius:12px;"><strong>{{ $when }}</strong><br>30 minutes, online</p>
        @if ($booking->meeting_link)
            <p style="margin:18px 0;"><a href="{{ $booking->meeting_link }}" style="{{ $btn }}background:#FDB714;color:#0c1e21;">Join the call</a></p>
            <p style="margin:0 0 16px;font-size:13px;color:#67787a;word-break:break-all;">{{ $booking->meeting_link }}</p>
        @else
            <p style="margin:0 0 16px;">We will send the meeting link before the call.</p>
        @endif
        @if ($booking->note_to_visitor)<p style="margin:0 0 16px;">{!! nl2br(e($booking->note_to_visitor)) !!}</p>@endif
        <p style="margin:0 0 16px;">A calendar invite is attached. Open it to add the call to your calendar.</p>

    @elseif ($kind === 'approved')
        <h1 style="margin:0 0 14px;font-size:24px;color:#0c1e21;">Added to your calendar</h1>
        <p style="margin:0 0 16px;padding:14px 18px;background:#e9f6fd;border-radius:12px;"><strong>{{ $when }}</strong><br>{{ $booking->name }} · {{ $booking->email }}</p>
        <p style="margin:0 0 16px;">Open the attached invite to add it to your calendar. It also appears in the admin: <a href="{{ $adminUrl }}" style="color:#2c99d4;">view booking</a>.</p>

    @elseif ($kind === 'declined')
        <h1 style="margin:0 0 14px;font-size:24px;color:#0c1e21;">About your call request</h1>
        <p style="margin:0 0 16px;">Hi {{ $first }}, thank you for your interest. Unfortunately we cannot take the call at {{ $when }}.</p>
        @if ($booking->decline_reason)<p style="margin:0 0 16px;">{!! nl2br(e($booking->decline_reason)) !!}</p>@endif
        <p style="margin:18px 0;"><a href="{{ $bookAgainUrl }}" style="{{ $btn }}background:#FDB714;color:#0c1e21;">Choose another time</a></p>

    @else
        <h1 style="margin:0 0 14px;font-size:24px;color:#0c1e21;">Your call was cancelled</h1>
        <p style="margin:0 0 16px;">Hi {{ $first }}, your discovery call on {{ $when }} has been cancelled. You are welcome to choose another time.</p>
        <p style="margin:18px 0;"><a href="{{ $bookAgainUrl }}" style="{{ $btn }}background:#FDB714;color:#0c1e21;">Choose another time</a></p>
    @endif

    <p style="margin:24px 0 0;">Warm regards,<br>Forward Edge Consulting</p>
    </td></tr>
</table>
</td></tr></table>
</body>
</html>
