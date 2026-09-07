<?php

namespace App\Enums;

enum HomepagePromoType: string
{
    case FeatureBand = 'feature_band';
    case PromoStrip = 'promo_strip';
    case BusinessCta = 'business_cta';
    case FeatureHighlight = 'feature_highlight';
    case Industry = 'industry';
    case CustomManufacturing = 'custom_manufacturing';
    case Stat = 'stat';

    public function label(): string
    {
        return match ($this) {
            self::FeatureBand => 'شريط الميزة',
            self::PromoStrip => 'شريط ترويجي',
            self::BusinessCta => 'دعوة الأعمال',
            self::FeatureHighlight => 'ميزة رئيسية',
            self::Industry => 'قطاع',
            self::CustomManufacturing => 'التصنيع حسب الطلب',
            self::Stat => 'إحصائية',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::FeatureBand => 'Feature Band',
            self::PromoStrip => 'Promo Strip',
            self::BusinessCta => 'Business CTA',
            self::FeatureHighlight => 'Feature Highlight',
            self::Industry => 'Industry',
            self::CustomManufacturing => 'Custom Manufacturing',
            self::Stat => 'Statistic',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
