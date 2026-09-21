<?php

namespace App\Support;

class HomepageWhyUsContentDefaults
{
    /**
     * @return array{title_ar: string, title_en: string, headline_ar: string, headline_en: string, highlight_ar: string, highlight_en: string}
     */
    public static function section(): array
    {
        return [
            'title_ar' => 'لماذا نحن',
            'title_en' => 'Why Us',
            'headline_ar' => 'ما يميز سمارت سيفتي',
            'headline_en' => 'What sets Smart Safety apart',
            'highlight_ar' => 'سمارت سيفتي',
            'highlight_en' => 'Smart Safety',
        ];
    }

    /**
     * @return list<array{title_ar: string, title_en: string, description_ar: string, description_en: string, icon: string, ordering: int, match_ar: list<string>, match_en: list<string>}>
     */
    public static function items(): array
    {
        return [
            [
                'title_ar' => 'كوادر عمانية متخصصة',
                'title_en' => 'Specialized Omani talent',
                'description_ar' => 'فريق عمل عماني متكامل ذو خبرة ومدرب على تنفيذ الأعمال في أقرب وقت ممكن',
                'description_en' => 'An integrated Omani team with experience and training to deliver work as quickly as possible',
                'icon' => 'flag',
                'ordering' => 0,
                'match_ar' => ['كوادر عمانية متخصصة'],
                'match_en' => ['Specialized Omani talent', 'Specialized Omani team'],
            ],
            [
                'title_ar' => 'تنفيذ سريع ودقيق',
                'title_en' => 'Fast and precise execution',
                'description_ar' => 'معدات عالية الدقة والأمان قابلة للتطوير في أي لحظة دون البدء من جديد',
                'description_en' => 'High-precision, safe equipment that can be upgraded at any time without starting from scratch',
                'icon' => 'bolt',
                'ordering' => 1,
                'match_ar' => ['تنفيذ سريع ودقيق'],
                'match_en' => ['Fast and precise execution'],
            ],
            [
                'title_ar' => 'دعم التنمية المستدامة',
                'title_en' => 'Support for sustainable development',
                'description_ar' => 'نساهم في تحقيق أهداف التنمية الاقتصادية بالسلطنة ورفع الميزان التجاري العماني',
                'description_en' => 'We contribute to Oman’s economic development goals and strengthening the national trade balance',
                'icon' => 'sprout',
                'ordering' => 2,
                'match_ar' => ['دعم التنمية المستدامة'],
                'match_en' => ['Support for sustainable development', 'Sustainable development'],
            ],
        ];
    }
}
