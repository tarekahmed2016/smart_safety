<?php

namespace App\Support;

class HomepageContactContentDefaults
{
    /**
     * @return array<string, string>
     */
    public static function companyFields(): array
    {
        return [
            'contact_section_title_ar' => 'هل لديك مشروع في ذهنك؟',
            'contact_section_title_en' => 'Have a project in mind?',
            'contact_section_subtitle_ar' => 'نحن هنا لتحويل أفكارك الصناعية إلى واقع ملموس بأعلى معايير الجودة.',
            'contact_section_subtitle_en' => 'We are here to turn your industrial ideas into reality with the highest quality standards.',
        ];
    }
}
