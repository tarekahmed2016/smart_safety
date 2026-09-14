<?php

namespace App\Enums\ContactMessages;

enum ContactRequestKind: string
{
    case General = 'general';
    case Quote = 'quote';
    case ProductInquiry = 'product_inquiry';

    public function labelAr(): string
    {
        return match ($this) {
            self::General => 'رسالة تواصل',
            self::Quote => 'طلب عرض سعر',
            self::ProductInquiry => 'استفسار منتج',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::General => 'Contact message',
            self::Quote => 'Quote request',
            self::ProductInquiry => 'Product inquiry',
        };
    }
}
