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
                'products_section_title_ar',
                'products_section_title_en',
                'products_homepage_limit',
            ],
            'industries' => [
                'industries_section_title_ar',
                'industries_section_title_en',
            ],
            'about' => [
                'about_section_title_ar',
                'about_section_title_en',
                'about_highlight_ar',
                'about_highlight_en',
                'about_cta_text_ar',
                'about_cta_text_en',
                'about_cta_url',
            ],
            'gallery' => [
                'gallery_section_title_ar',
                'gallery_section_title_en',
            ],
            'contact' => [
                'contact_section_title_ar',
                'contact_section_title_en',
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
                'title_ar',
                'title_en',
                'subtitle_ar',
                'subtitle_en',
            ],
            'vision_mission', 'goals', 'why_us', 'team_members', 'clients_partners' => [
                'title_ar',
                'title_en',
                ...($key === 'goals' || $key === 'vision_mission' || $key === 'why_us' ? ['headline_ar', 'headline_en'] : []),
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
