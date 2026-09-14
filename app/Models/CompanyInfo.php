<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable([
    'name_ar',
    'name_en',
    'company_name_text_color',
    'company_name_font_family_ar',
    'company_name_font_family_en',
    'company_name_font_size_ar',
    'company_name_font_size_en',
    'company_name_font_weight',
    'phone',
    'email',
    'hero_title_ar',
    'hero_title_en',
    'hero_description_ar',
    'hero_description_en',
    'about_ar',
    'about_en',
    'vision_ar',
    'vision_en',
    'mission_ar',
    'mission_en',
    'address_ar',
    'address_en',
    'google_maps_embed_url',
    'website',
    'facebook',
    'instagram',
    'linkedin',
    'x_twitter',
    'youtube',
    'tiktok',
    'snapchat',
    'whatsapp',
    'hero_highlight_ar',
    'hero_highlight_en',
    'hero_primary_cta_text_ar',
    'hero_primary_cta_text_en',
    'hero_primary_cta_url',
    'hero_secondary_cta_text_ar',
    'hero_secondary_cta_text_en',
    'hero_secondary_cta_url',
    'products_section_title_ar',
    'products_section_title_en',
    'products_homepage_limit',
    'industries_section_title_ar',
    'industries_section_title_en',
    'about_section_title_ar',
    'about_section_title_en',
    'about_highlight_ar',
    'about_highlight_en',
    'about_cta_text_ar',
    'about_cta_text_en',
    'about_cta_url',
    'gallery_section_title_ar',
    'gallery_section_title_en',
    'contact_section_title_ar',
    'contact_section_title_en',
    'contact_section_subtitle_ar',
    'contact_section_subtitle_en',
    'footer_description_ar',
    'footer_description_en',
    'footer_newsletter_title_ar',
    'footer_newsletter_title_en',
    'footer_newsletter_description_ar',
    'footer_newsletter_description_en',
    'footer_newsletter_button_ar',
    'footer_newsletter_button_en',
    'footer_newsletter_placeholder_ar',
    'footer_newsletter_placeholder_en',
    'footer_copyright_ar',
    'footer_copyright_en',
    'theme_primary_color',
    'theme_dark_color',
    'theme_heading_text_color',
    'theme_body_text_color',
    'theme_muted_text_color',
    'theme_nav_text_color',
    'theme_nav_hover_text_color',
    'theme_hero_text_color',
    'theme_on_dark_text_color',
    'custom_css',
    'custom_js',
])]
class CompanyInfo extends Model
{
    protected $table = 'company_info';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'products_homepage_limit' => 'integer',
        ];
    }

    /**
     * @return MorphOne<Attachment, $this>
     */
    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable')
            ->where(function ($query) {
                $query->whereNull('collection')->orWhere('collection', 'default');
            });
    }

    /**
     * @return MorphOne<Attachment, $this>
     */
    public function aboutAttachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable')
            ->where('collection', 'about');
    }

    /**
     * @return MorphMany<ActivityLog, $this>
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
