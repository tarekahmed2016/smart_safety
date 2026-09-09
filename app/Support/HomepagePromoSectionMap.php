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
            'products',
            'services',
            'custom_manufacturing',
            'industries',
            'about',
            'team_members',
            'clients_partners',
            'gallery',
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
            'about' => [HomepagePromoType::Stat],
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
            'services', 'team_members', 'clients_partners' => [
                'title_ar',
                'title_en',
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
            'team_members' => 'team-members.index',
            'clients_partners' => 'clients-partners.index',
            'gallery' => 'projects.index',
            'about' => 'company-info.index',
            default => null,
        };
    }
}
