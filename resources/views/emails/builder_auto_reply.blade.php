<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $subjectLine ?? 'Thank you' }}</title>
</head>

<body style="font-family: 'Helvetica Neue', Arial, sans-serif; background: #f5f7fb; padding: 24px; color: #0f172a;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation"
                    style="background:#ffffff;border-radius:18px;box-shadow:0 15px 35px rgba(15,23,42,.08);padding:32px;">
                     <tr>
                        <td style="text-align:center;padding-bottom:24px;">
                            <img src="{{ asset('frontend/assets/images/logos/logo.png') }}" alt="Forward Edge"
                                width="120" style="display:block;margin:0 auto 16px;">
                            <h1 style="margin:0;font-size:24px;color:#0c1e21;">{{ $subjectLine }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:16px;line-height:1.6;">
                               <p style="margin:0 0 18px;">
                                Hi {{ $firstName }},
                            </p>
                            {!! nl2br(e($bodyCopy ?: 'We received your submission and will be in touch shortly.')) !!}
                            <p style="margin-top:24px;">Warm regards,<br>Forward Edge Consulting</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
