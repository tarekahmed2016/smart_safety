<?php

namespace App\Support;

use App\Enums\ContactMessages\ContactRequestKind;
use App\Models\ContactMessage;

final class ContactMessageClassifier
{
    public static function kind(ContactMessage $message): ContactRequestKind
    {
        $subject = trim((string) $message->subject);
        $haystack = $subject.' '.(string) $message->message;

        if (self::productName($message) !== null) {
            return ContactRequestKind::ProductInquiry;
        }

        if (preg_match('/عرض\s*سعر|quote\s*request|\bquote\b|طلب\s*عرض/iu', $haystack)) {
            return ContactRequestKind::Quote;
        }

        return ContactRequestKind::General;
    }

    public static function productName(ContactMessage $message): ?string
    {
        $subject = trim((string) $message->subject);

        if ($subject === '') {
            return null;
        }

        if (preg_match('/^استفسار عن المنتج:\s*(.+)$/u', $subject, $matches) === 1) {
            $name = trim($matches[1]);

            return $name !== '' ? $name : null;
        }

        if (preg_match('/^Inquiry about\s+(.+)$/u', $subject, $matches) === 1) {
            $name = trim($matches[1]);

            return $name !== '' ? $name : null;
        }

        return null;
    }
}
