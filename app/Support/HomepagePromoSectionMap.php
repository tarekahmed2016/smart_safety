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
     * Promo cards do not own section-wide settings; those live on Homepage Sections.
     *
     * @return list<string>
     */
    public static function companySettingFields(string $key): array
    {
        return [];
    }

    /**
     * Promo cards do not own section-wide settings; those live on Homepage Sections.
     *
     * @return list<string>
     */
    public static function sectionSettingFields(string $key): array
    {
        return [];
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
