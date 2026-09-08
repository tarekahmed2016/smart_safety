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
            'products' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'المنتجات',
                'nav_label_en' => 'Products',
                'nav_order' => 30,
                'anchor_id' => 'products',
            ],
            'services' => [
                'show_in_navigation' => false,
                'nav_label_ar' => 'الخدمات',
                'nav_label_en' => 'Services',
                'nav_order' => 35,
                'anchor_id' => 'services',
            ],
            'custom_manufacturing' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'التصنيع حسب الطلب',
                'nav_label_en' => 'Custom Manufacturing',
                'nav_order' => 40,
                'anchor_id' => 'custom-manufacturing',
            ],
            'industries' => [
                'show_in_navigation' => false,
                'nav_label_ar' => 'القطاعات',
                'nav_label_en' => 'Industries',
                'nav_order' => 45,
                'anchor_id' => 'industries',
            ],
            'gallery' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'معرض الصور',
                'nav_label_en' => 'Gallery',
                'nav_order' => 50,
                'anchor_id' => 'gallery',
            ],
            'team_members' => [
                'show_in_navigation' => false,
                'nav_label_ar' => 'فريق العمل',
                'nav_label_en' => 'Team',
                'nav_order' => 55,
                'anchor_id' => 'team',
            ],
            'clients_partners' => [
                'show_in_navigation' => false,
                'nav_label_ar' => 'العملاء والشركاء',
                'nav_label_en' => 'Clients & Partners',
                'nav_order' => 60,
                'anchor_id' => 'clients-partners',
            ],
            'contact' => [
                'show_in_navigation' => true,
                'nav_label_ar' => 'تواصل معنا',
                'nav_label_en' => 'Contact Us',
                'nav_order' => 100,
                'anchor_id' => 'contact',
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
