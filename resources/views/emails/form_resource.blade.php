<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>{{ $resourceTitle }}</title></head>
<body style="margin:0;padding:24px;background:#f4f7f8;font-family:'Helvetica Neue',Arial,sans-serif;color:#364e52;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" role="presentation" style="max-width:100%;background:#ffffff;border-radius:16px;padding:32px;">
        <tr><td style="padding-bottom:20px;"><img src="{{ asset('frontend/assets/images/logos/logo.png') }}" alt="Forward Edge Consulting" width="120" style="display:block;"></td></tr>
        <tr><td style="font-size:16px;line-height:1.65;">
            <p style="margin:0 0 16px;">Hi {{ $firstName }},</p>
            {!! nl2br(e($bodyCopy)) !!}
            <p style="margin:28px 0;"><a href="{{ $downloadUrl }}" style="display:inline-block;background:#FDB714;color:#0c1e21;font-weight:700;text-decoration:none;padding:14px 26px;border-radius:50px;">Download {{ $resourceTitle }}</a></p>
            <p style="margin:0 0 8px;font-size:13px;color:#67787a;">The button stops working after 30 days. If it does not open, copy this link into your browser:<br><span style="word-break:break-all;">{{ $downloadUrl }}</span></p>
            <p style="margin:24px 0 0;">Warm regards,<br>Forward Edge Consulting</p>
        </td></tr>
    </table>
</td></tr></table>
</body>
</html>
