<?php

namespace App\Support;

use App\Enums\HomepageSectionType;

class HomepageSectionDefaults
{
    /**
     * @return list<array{key: string, name: string, type: HomepageSectionType, ordering: int, is_visible: bool, title_ar: string|null, title_en: string|null, settings: array<string, mixed>|null}>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'hero',
                'name' => 'Hero',
                'type' => HomepageSectionType::Hero,
                'ordering' => 1,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'features',
                'name' => 'Features',
                'type' => HomepageSectionType::Features,
                'ordering' => 2,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'products',
                'name' => 'Products',
                'type' => HomepageSectionType::Products,
                'ordering' => 3,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'services',
                'name' => 'Services',
                'type' => HomepageSectionType::Services,
                'ordering' => 4,
                'is_visible' => false,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'custom_manufacturing',
                'name' => 'Custom Manufacturing',
                'type' => HomepageSectionType::CustomManufacturing,
                'ordering' => 5,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'industries',
                'name' => 'Industries',
                'type' => HomepageSectionType::Industries,
                'ordering' => 6,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'about',
                'name' => 'About',
                'type' => HomepageSectionType::About,
                'ordering' => 7,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'team_members',
                'name' => 'Team Members',
                'type' => HomepageSectionType::TeamMembers,
                'ordering' => 8,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'clients_partners',
                'name' => 'Clients & Partners',
                'type' => HomepageSectionType::ClientsPartners,
                'ordering' => 9,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'gallery',
                'name' => 'Gallery',
                'type' => HomepageSectionType::Gallery,
                'ordering' => 10,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => ['max_items' => 8],
            ],
            [
                'key' => 'contact_cta',
                'name' => 'Contact CTA',
                'type' => HomepageSectionType::ContactCta,
                'ordering' => 11,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'contact',
                'name' => 'Contact Form',
                'type' => HomepageSectionType::ContactForm,
                'ordering' => 12,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
        ];
    }
}
