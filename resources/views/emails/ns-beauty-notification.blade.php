<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $notificationTitle }}</title>
</head>
<body style="margin:0;padding:0;background:#f7f1ea;font-family:Arial,sans-serif;color:#2f2118;">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Email Notification Template
    |--------------------------------------------------------------------------
    | Purpose:
    | - Provides a clean email layout for NS Beauty notifications.
    |--------------------------------------------------------------------------
    --}}

    <div style="max-width:620px;margin:0 auto;padding:30px 18px;">
        <div style="background:#fffaf4;border-radius:24px;padding:28px;border:1px solid #eadfd4;">
            <h2 style="margin:0 0 12px;color:#5d3f2c;">
                NS &amp; Beauty
            </h2>

            <h3 style="margin:0 0 16px;color:#2f2118;">
                {{ $notificationTitle }}
            </h3>

            <p style="font-size:15px;line-height:1.7;color:#6f5d50;">
                {{ $notificationMessage }}
            </p>

            @if ($notificationLink)
                <p style="margin-top:24px;">
                    <a
                        href="{{ url($notificationLink) }}"
                        style="display:inline-block;background:#5d3f2c;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:999px;font-weight:bold;"
                    >
                        Open Notification
                    </a>
                </p>
            @endif

            <p style="margin-top:28px;font-size:12px;color:#9b8b7d;">
                This is an automated message from NS &amp; Beauty.
            </p>
        </div>
    </div>

</body>
</html>