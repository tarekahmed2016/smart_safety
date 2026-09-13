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
        $services = HomepageServicesContentDefaults::section();
        $whyUs = HomepageWhyUsContentDefaults::section();

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
                'key' => 'about',
                'name' => 'About',
                'type' => HomepageSectionType::About,
                'ordering' => 3,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'why_us',
                'name' => 'Why Us',
                'type' => HomepageSectionType::WhyUs,
                'ordering' => 4,
                'is_visible' => true,
                'title_ar' => $whyUs['title_ar'],
                'title_en' => $whyUs['title_en'],
                'settings' => [
                    'headline_ar' => $whyUs['headline_ar'],
                    'headline_en' => $whyUs['headline_en'],
                    'highlight_ar' => $whyUs['highlight_ar'],
                    'highlight_en' => $whyUs['highlight_en'],
                ],
            ],
            [
                'key' => 'services',
                'name' => 'Services',
                'type' => HomepageSectionType::Services,
                'ordering' => 5,
                'is_visible' => true,
                'title_ar' => $services['title_ar'],
                'title_en' => $services['title_en'],
                'settings' => [
                    'highlight_ar' => $services['highlight_ar'],
                    'highlight_en' => $services['highlight_en'],
                    'subtitle_ar' => $services['subtitle_ar'],
                    'subtitle_en' => $services['subtitle_en'],
                ],
            ],
            [
                'key' => 'products',
                'name' => 'Products',
                'type' => HomepageSectionType::Products,
                'ordering' => 6,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'vision_mission',
                'name' => 'Vision & Mission',
                'type' => HomepageSectionType::VisionMission,
                'ordering' => 7,
                'is_visible' => true,
                'title_ar' => 'رؤيتنا ورسالتنا',
                'title_en' => 'Vision & Mission',
                'settings' => [
                    'headline_ar' => 'نحو صناعة متكاملة',
                    'headline_en' => 'Towards an integrated industry',
                ],
            ],
            [
                'key' => 'goals',
                'name' => 'Goals',
                'type' => HomepageSectionType::Goals,
                'ordering' => 8,
                'is_visible' => true,
                'title_ar' => 'أهدافنا',
                'title_en' => 'Our Goals',
                'settings' => [
                    'headline_ar' => 'نسعى لتحقيق التميز',
                    'headline_en' => 'We strive for excellence',
                ],
            ],
            [
                'key' => 'team_members',
                'name' => 'Team Members',
                'type' => HomepageSectionType::TeamMembers,
                'ordering' => 9,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'clients_partners',
                'name' => 'Clients & Partners',
                'type' => HomepageSectionType::ClientsPartners,
                'ordering' => 10,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'gallery',
                'name' => 'Gallery',
                'type' => HomepageSectionType::Gallery,
                'ordering' => 11,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => ['max_items' => 8],
            ],
            [
                'key' => 'custom_manufacturing',
                'name' => 'Custom Manufacturing',
                'type' => HomepageSectionType::CustomManufacturing,
                'ordering' => 12,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'industries',
                'name' => 'Industries',
                'type' => HomepageSectionType::Industries,
                'ordering' => 13,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'contact_cta',
                'name' => 'Contact CTA',
                'type' => HomepageSectionType::ContactCta,
                'ordering' => 14,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
            [
                'key' => 'contact',
                'name' => 'Contact Form',
                'type' => HomepageSectionType::ContactForm,
                'ordering' => 15,
                'is_visible' => true,
                'title_ar' => null,
                'title_en' => null,
                'settings' => null,
            ],
        ];
    }

    public static function usesStandaloneSectionTitle(string $key): bool
    {
        return ! ($key === 'hero' || $key === 'features');
    }
}
