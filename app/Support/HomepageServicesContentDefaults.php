<?php

namespace App\Support;

class HomepageServicesContentDefaults
{
    /**
     * @return array{title_ar: string, title_en: string, highlight_ar: string, highlight_en: string, subtitle_ar: string, subtitle_en: string}
     */
    public static function section(): array
    {
        return [
            'title_ar' => 'خدماتنا',
            'title_en' => 'Our Services',
            'highlight_ar' => 'خدماتنا',
            'highlight_en' => 'Services',
            'subtitle_ar' => '',
            'subtitle_en' => '',
        ];
    }

    /**
     * @return list<array{name_ar: string, name_en: string, description_ar: string, description_en: string, icon: string, ordering: int, match_ar: list<string>, match_en: list<string>}>
     */
    public static function items(): array
    {
        return [
            [
                'name_ar' => 'التصنيع',
                'name_en' => 'Manufacturing',
                'description_ar' => 'حلول تصنيع صناعية وتوفير خطوط الإنتاج.',
                'description_en' => 'Industrial manufacturing solutions and production-line supply.',
                'icon' => 'industry',
                'ordering' => 0,
                'match_ar' => ['التصنيع', 'التصنيع الصناعي وخطوط الإنتاج'],
                'match_en' => ['Manufacturing', 'Industrial manufacturing and production lines'],
            ],
            [
                'name_ar' => 'الصناعة البلاستيكية',
                'name_en' => 'Plastic industry',
                'description_ar' => 'مثل الأغطية والعلب ومواد التغليف.',
                'description_en' => 'Such as covers, cans, and packaging materials.',
                'icon' => 'packing',
                'ordering' => 1,
                'match_ar' => ['الصناعة البلاستيكية', 'حلول الصناعة البلاستيكية'],
                'match_en' => ['Plastic industry', 'Plastic industry solutions'],
            ],
        ];
    }
}
