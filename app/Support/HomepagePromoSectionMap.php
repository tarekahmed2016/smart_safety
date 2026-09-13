<?php

namespace App\Support;

use App\Enums\HomepagePromoType;

class HomepagePromoSectionMap
{
    /**
     * @return list<string>
     */
    public static function sectionKeys(): array
    {
        return [
            'features',
            'about',
            'why_us',
            'services',
            'products',
            'vision_mission',
            'goals',
            'team_members',
            'clients_partners',
            'gallery',
            'custom_manufacturing',
            'industries',
            'contact_cta',
            'contact',
        ];
    }

    /**
     * @return list<HomepagePromoType>
     */
    public static function promoTypesForSection(string $key): array
    {
        return match ($key) {
            'features' => [HomepagePromoType::FeatureHighlight],
            'custom_manufacturing' => [HomepagePromoType::CustomManufacturing],
            'industries' => [HomepagePromoType::Industry],
            'about' => [HomepagePromoType::Stat, HomepagePromoType::AboutHighlight],
            'why_us' => [HomepagePromoType::WhyUsHighlight],
            'contact_cta' => [HomepagePromoType::BusinessCta],
            default => [],
        };
    }

    public static function defaultPromoType(string $key): ?HomepagePromoType
    {
        return self::promoTypesForSection($key)[0] ?? null;
    }

    public static function canManagePromos(string $key): bool
    {
        return self::defaultPromoType($key) !== null;
    }

    /**
     * @return list<string>
     */
    public static function companySettingFields(string $key): array
    {
        return match ($key) {
            'products' => [
                'products_homepage_limit',
            ],
            'about' => [
                'about_highlight_ar',
                'about_highlight_en',
                'about_cta_text_ar',
                'about_cta_text_en',
                'about_cta_url',
            ],
            'contact' => [
                'contact_section_subtitle_ar',
                'contact_section_subtitle_en',
            ],
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    public static function sectionSettingFields(string $key): array
    {
        return match ($key) {
            'services' => [
                'subtitle_ar',
                'subtitle_en',
            ],
            'vision_mission', 'goals', 'why_us' => [
                'headline_ar',
                'headline_en',
                ...($key === 'why_us' ? ['highlight_ar', 'highlight_en'] : []),
            ],
            'gallery' => [
                'max_items',
            ],
            default => [],
        };
    }

    public static function manageRouteName(string $key): ?string
    {
        return match ($key) {
            'products' => 'products.index',
            'services' => 'services.index',
            'goals' => 'company-goals.index',
            'team_members' => 'team-members.index',
            'clients_partners' => 'clients-partners.index',
            'gallery' => 'projects.index',
            'about' => 'company-info.index',
            'vision_mission' => 'company-info.index',
            default => null,
        };
    }
}
