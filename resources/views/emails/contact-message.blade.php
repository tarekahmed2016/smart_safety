<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسالة تواصل جديدة</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Tahoma,Arial,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:24px 28px;background:#0f766e;color:#ffffff;">
                            <h1 style="margin:0;font-size:22px;line-height:1.5;">رسالة تواصل جديدة</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:0 0 16px;">
                                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                                            <span dir="rtl">نوع الطلب</span>
                                            <span dir="ltr"> / Request type</span>
                                        </div>
                                        <div style="font-size:16px;font-weight:700;">
                                            <span dir="rtl">{{ $kindLabelAr }}</span>
                                            <span dir="ltr"> / {{ $kindLabelEn }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 16px;">
                                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                                            <span dir="rtl">حالة الطلب</span>
                                            <span dir="ltr"> / Request status</span>
                                        </div>
                                        <div style="font-size:16px;font-weight:700;">
                                            <span dir="rtl">{{ $statusLabelAr }}</span>
                                            <span dir="ltr"> / {{ $statusLabelEn }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 16px;">
                                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                                            <span dir="rtl">اسم العميل</span>
                                            <span dir="ltr"> / Customer name</span>
                                        </div>
                                        <div dir="auto" style="font-size:16px;">{{ $contactMessage->name }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 16px;">
                                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                                            <span dir="rtl">البريد</span>
                                            <span dir="ltr"> / Email</span>
                                        </div>
                                        <div dir="ltr" style="font-size:16px;">{{ $contactMessage->email ?: '—' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 16px;">
                                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                                            <span dir="rtl">الهاتف</span>
                                            <span dir="ltr"> / Phone</span>
                                        </div>
                                        <div dir="ltr" style="font-size:16px;">{{ $contactMessage->phone ?: '—' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 16px;">
                                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                                            <span dir="rtl">الموضوع</span>
                                            <span dir="ltr"> / Subject</span>
                                        </div>
                                        <div dir="auto" style="font-size:16px;">{{ $contactMessage->subject ?: '—' }}</div>
                                    </td>
                                </tr>
                                @if ($productName)
                                    <tr>
                                        <td style="padding:0 0 16px;">
                                            <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                                                <span dir="rtl">المنتج</span>
                                                <span dir="ltr"> / Product</span>
                                            </div>
                                            <div dir="auto" style="font-size:16px;">{{ $productName }}</div>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:0 0 16px;">
                                        <div style="font-size:12px;color:#6b7280;margin-bottom:8px;">
                                            <span dir="rtl">الرسالة</span>
                                            <span dir="ltr"> / Message</span>
                                        </div>
                                        <div dir="auto" style="font-size:15px;line-height:1.8;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:16px;white-space:normal;">
                                            {!! nl2br(e($contactMessage->message), false) !!}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                                            <span dir="rtl">تاريخ الإرسال</span>
                                            <span dir="ltr"> / Sent at</span>
                                        </div>
                                        <div dir="ltr" style="font-size:16px;">{{ $sentAt }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
