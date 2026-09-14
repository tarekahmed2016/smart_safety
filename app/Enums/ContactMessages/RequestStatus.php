<?php

namespace App\Enums\ContactMessages;

enum RequestStatus: string
{
    case New = 'new';
    case UnderReview = 'under_review';
    case Contacted = 'contacted';
    case QuoteSent = 'quote_sent';
    case Agreed = 'agreed';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public function labelAr(): string
    {
        return match ($this) {
            self::New => 'طلب جديد',
            self::UnderReview => 'قيد المراجعة',
            self::Contacted => 'تم التواصل مع العميل',
            self::QuoteSent => 'تم إرسال عرض سعر',
            self::Agreed => 'تم الاتفاق',
            self::Rejected => 'مرفوض',
            self::Completed => 'مكتمل',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::New => 'New request',
            self::UnderReview => 'Under review',
            self::Contacted => 'Customer contacted',
            self::QuoteSent => 'Quote sent',
            self::Agreed => 'Agreed',
            self::Rejected => 'Rejected',
            self::Completed => 'Completed',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<array{value: string, label_ar: string, label_en: string, name: string}>
     */
    public static function toArray(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label_ar' => $case->labelAr(),
                'label_en' => $case->labelEn(),
                'name' => $case->name,
            ],
            self::cases()
        );
    }
}
