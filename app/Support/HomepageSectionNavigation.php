<?php

namespace App\Support;

class HomepageSectionNavigation
{
    /**
     * @return list<string>
     */
    public static function navigableKeys(): array
    {
        return [
            'hero',
            'about',
            'why_us',
            'vision_mission',
            'goals',
            'products',
            'services',
            'custom_manufacturing',
            'industries',
            'gallery',
            'team_members',
            'clients_partners',
            'contact',
        ];
    }

    public static function isNavigable(string $key): bool
    {
        return in_array($key, self::navigableKeys(), true);
    }

    /**
     * @return array<string, array{show_in_navigation: bool, nav_label_ar: string, nav_label_en: string, nav_order: int, anchor_id: string}>
     */
    public static function defaultsByKey(): array
    {
        return [
            'hero' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'الرئيسية',
                'nav_label_en' => 'Home',
                'nav_order' => 10,
                'anchor_id' => 'home',
            ],
            'about' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'من نحن',
                'nav_label_en' => 'About Us',
                'nav_order' => 20,
                'anchor_id' => 'about',
            ],
            'why_us' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'لماذا نحن',
                'nav_label_en' => 'Why Us',
                'nav_order' => 25,
                'anchor_id' => 'why-us',
            ],
            'services' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'خدماتنا',
                'nav_label_en' => 'Our Services',
                'nav_order' => 30,
                'anchor_id' => 'services',
            ],
            'products' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'منتجاتنا',
                'nav_label_en' => 'Our Products',
                'nav_order' => 40,
                'anchor_id' => 'products',
            ],
            'vision_mission' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'رؤيتنا',
                'nav_label_en' => 'Our Vision',
                'nav_order' => 50,
                'anchor_id' => 'vision-mission',
            ],
            'goals' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'أهدافنا',
                'nav_label_en' => 'Our Goals',
                'nav_order' => 60,
                'anchor_id' => 'goals',
            ],
            'clients_partners' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'عملاؤنا',
                'nav_label_en' => 'Our Clients',
                'nav_order' => 70,
                'anchor_id' => 'clients-partners',
            ],
            'gallery' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'معرض الصور',
                'nav_label_en' => 'Gallery',
                'nav_order' => 80,
                'anchor_id' => 'gallery',
            ],
            'custom_manufacturing' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'التصنيع حسب الطلب',
                'nav_label_en' => 'Custom Manufacturing',
                'nav_order' => 90,
                'anchor_id' => 'custom-manufacturing',
            ],
            'contact' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'تواصل معنا',
                'nav_label_en' => 'Contact Us',
                'nav_order' => 100,
                'anchor_id' => 'contact-form',
            ],
            'industries' => [
                'show_in_navigation' => false,
                'nav_label_ar' => 'القطاعات',
                'nav_label_en' => 'Industries',
                'nav_order' => 110,
                'anchor_id' => 'industries',
            ],
            'team_members' => [
                'show_in_navigation' => false,
                'nav_label_ar' => 'فريق العمل',
                'nav_label_en' => 'Team',
                'nav_order' => 120,
                'anchor_id' => 'team',
            ],
        ];
    }

    /**
     * @return array{nav_label_ar: string, nav_label_en: string, anchor_id: string}
     */
    public static function fallbackForKey(string $key): array
    {
        $defaults = self::defaultsByKey()[$key] ?? [
            'nav_label_ar' => '',
            'nav_label_en' => '',
            'anchor_id' => $key,
        ];

        return [
            'nav_label_ar' => $defaults['nav_label_ar'],
            'nav_label_en' => $defaults['nav_label_en'],
            'anchor_id' => $defaults['anchor_id'],
        ];
    }
}
