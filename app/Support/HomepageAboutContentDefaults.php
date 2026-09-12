<?php

namespace App\Support;

class HomepageAboutContentDefaults
{
    /**
     * @return array<string, string>
     */
    public static function companyFields(): array
    {
        return [
            'about_section_title_ar' => 'شركة عمانية بكوادر متميزة',
            'about_section_title_en' => 'An Omani company with distinguished talent',
            'about_highlight_ar' => 'متميزة',
            'about_highlight_en' => 'distinguished',
            'about_ar' => 'الصناعة الإبداعية شركة بإدارة عمانية وكادر مميز من مهندسين عمانيين ذوي خبرة أكثر من عشر سنوات. جاءت فكرة الشركة لتلائم الخطط الاستراتيجية الصناعية في السلطنة، لتساهم في وصولها إلى مصاف الدول المتقدمة وتحفّز التقدم في الصناعات التحويلية الوطنية. نحن مصنع مختص في توفير خطوط الإنتاج الصناعية مع توفير المنتجات البلاستيكية للعلامات التجارية، ونساهم في تحقيق أهداف التنمية الاقتصادية عبر تلبية احتياجات السوق المحلي والمصانع الوطنية مع السعي للتوسع إقليميًا وعالميًا.',
            'about_en' => 'Creative Industry is an Omani-managed company with a distinguished team of Omani engineers with more than ten years of experience. The company was founded to align with the Sultanate’s industrial strategy, help Oman reach advanced economies, and accelerate national transformative industries. We specialize in supplying industrial production lines and plastic products for brands, support economic development goals by serving local market and national factory needs, and pursue regional and global expansion.',
        ];
    }

    /**
     * @return list<array{title_ar: string, title_en: string, description_ar: string, description_en: string, icon: string|null, ordering: int}>
     */
    public static function stats(): array
    {
        return [
            [
                'title_ar' => '10+',
                'title_en' => '10+',
                'description_ar' => 'سنوات خبرة',
                'description_en' => 'Years of experience',
                'icon' => null,
                'ordering' => 0,
            ],
            [
                'title_ar' => '100%',
                'title_en' => '100%',
                'description_ar' => 'كوادر عمانية',
                'description_en' => 'Omani workforce',
                'icon' => null,
                'ordering' => 1,
            ],
        ];
    }

    /**
     * @return list<array{title_ar: string, title_en: string, description_ar: string, description_en: string, icon: string, ordering: int}>
     */
    public static function highlights(): array
    {
        return [
            [
                'title_ar' => 'معايير عالمية',
                'title_en' => 'World-class standards',
                'description_ar' => 'وحدات صناعية وفق أحدث المواصفات',
                'description_en' => 'Industrial units built to the latest specifications',
                'icon' => 'quality',
                'ordering' => 3,
            ],
            [
                'title_ar' => 'تقنيات متطورة',
                'title_en' => 'Advanced technologies',
                'description_ar' => 'ابتكار صناعي وتقنية معلومات',
                'description_en' => 'Industrial innovation and information technology',
                'icon' => 'flexible',
                'ordering' => 4,
            ],
            [
                'title_ar' => 'صحة وسلامة',
                'title_en' => 'Health and safety',
                'description_ar' => 'بيئة عمل آمنة ومعتمدة',
                'description_en' => 'A safe and certified work environment',
                'icon' => 'shield',
                'ordering' => 5,
            ],
            [
                'title_ar' => 'انتشار عالمي',
                'title_en' => 'Global reach',
                'description_ar' => 'أسواق محلية وإقليمية ودولية',
                'description_en' => 'Local, regional, and international markets',
                'icon' => 'globe',
                'ordering' => 6,
            ],
        ];
    }
}
