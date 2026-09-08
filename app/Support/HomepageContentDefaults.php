<?php

namespace App\Support;

class HomepageContentDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function companyInfoFields(): array
    {
        return [
            'hero_highlight_ar' => 'مستقبل أفضل',
            'hero_highlight_en' => 'better future',
            'hero_primary_cta_text_ar' => 'اطلب عرض سعر',
            'hero_primary_cta_text_en' => 'Request a quote',
            'hero_primary_cta_url' => '#contact',
            'hero_secondary_cta_text_ar' => 'استعرض منتجاتنا',
            'hero_secondary_cta_text_en' => 'Explore our products',
            'hero_secondary_cta_url' => '#products',
            'products_section_title_ar' => 'منتجاتنا',
            'products_section_title_en' => 'Our products',
            'products_homepage_limit' => 8,
            'industries_section_title_ar' => 'قطاعات نلبي احتياجاتها',
            'industries_section_title_en' => 'Industries we serve',
            'about_section_title_ar' => 'نبذة عن المصنع',
            'about_section_title_en' => 'About the factory',
            'about_cta_text_ar' => 'المزيد عنا',
            'about_cta_text_en' => 'More about us',
            'about_cta_url' => '#contact',
            'gallery_section_title_ar' => 'معرض الصور',
            'gallery_section_title_en' => 'Gallery',
            'contact_section_title_ar' => 'تواصل معنا',
            'contact_section_title_en' => 'Contact Us',
            'contact_section_subtitle_ar' => 'تواصل معنا باستخدام البيانات أدناه أو أرسل لنا رسالة.',
            'contact_section_subtitle_en' => 'Reach out to us using the details below or send us a message.',
            'footer_description_ar' => '',
            'footer_description_en' => '',
            'footer_newsletter_title_ar' => 'ابق على اطلاع',
            'footer_newsletter_title_en' => 'Stay in the Loop',
            'footer_newsletter_description_ar' => 'اشترك لتصلك آخر الأخبار والعروض.',
            'footer_newsletter_description_en' => 'Subscribe for updates, offers, and news from our brand.',
            'footer_newsletter_button_ar' => 'اشتراك',
            'footer_newsletter_button_en' => 'Subscribe',
            'footer_newsletter_placeholder_ar' => 'أدخل بريدك الإلكتروني',
            'footer_newsletter_placeholder_en' => 'Enter your email',
            'footer_copyright_ar' => '© {year} {company}. جميع الحقوق محفوظة.',
            'footer_copyright_en' => '© {year} {company}. All rights reserved.',
        ];
    }
}
