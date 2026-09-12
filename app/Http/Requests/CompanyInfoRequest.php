<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SanitizesRichTextInput;
use App\Rules\SafeHttpUrl;
use App\Support\CompanyNameBrandTypography;
use App\Support\RichTextSanitizer;
use App\Support\SafeRasterImage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompanyInfoRequest extends FormRequest
{
    use SanitizesRichTextInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullableBrandFields = [
            'company_name_font_family_ar',
            'company_name_font_family_en',
            'company_name_font_size_ar',
            'company_name_font_size_en',
            'company_name_font_weight',
            'company_name_text_color',
        ];

        foreach ($nullableBrandFields as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        $this->sanitizeRichTextInput();
    }

    /**
     * @return list<string>
     */
    protected function richTextFields(): array
    {
        return RichTextSanitizer::COMPANY_INFO_FIELDS;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            ...CompanyNameBrandTypography::rules(),
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'hero_title_ar' => ['nullable', 'string', 'max:255'],
            'hero_title_en' => ['nullable', 'string', 'max:255'],
            'hero_description_ar' => ['nullable', 'string', 'max:15000'],
            'hero_description_en' => ['nullable', 'string', 'max:15000'],
            'about_ar' => ['nullable', 'string', 'max:15000'],
            'about_en' => ['nullable', 'string', 'max:15000'],
            'vision_ar' => ['nullable', 'string', 'max:15000'],
            'vision_en' => ['nullable', 'string', 'max:15000'],
            'mission_ar' => ['nullable', 'string', 'max:15000'],
            'mission_en' => ['nullable', 'string', 'max:15000'],
            'address_ar' => ['nullable', 'string', 'max:1000'],
            'address_en' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', new SafeHttpUrl],
            'facebook' => ['nullable', new SafeHttpUrl],
            'instagram' => ['nullable', new SafeHttpUrl],
            'linkedin' => ['nullable', new SafeHttpUrl],
            'x_twitter' => ['nullable', new SafeHttpUrl],
            'youtube' => ['nullable', new SafeHttpUrl],
            'tiktok' => ['nullable', new SafeHttpUrl],
            'snapchat' => ['nullable', new SafeHttpUrl],
            'whatsapp' => ['nullable', new SafeHttpUrl],
            'hero_highlight_ar' => ['nullable', 'string', 'max:255'],
            'hero_highlight_en' => ['nullable', 'string', 'max:255'],
            'hero_primary_cta_text_ar' => ['nullable', 'string', 'max:255'],
            'hero_primary_cta_text_en' => ['nullable', 'string', 'max:255'],
            'hero_primary_cta_url' => ['nullable', 'string', 'max:500'],
            'hero_secondary_cta_text_ar' => ['nullable', 'string', 'max:255'],
            'hero_secondary_cta_text_en' => ['nullable', 'string', 'max:255'],
            'hero_secondary_cta_url' => ['nullable', 'string', 'max:500'],
            'products_section_title_ar' => ['nullable', 'string', 'max:255'],
            'products_section_title_en' => ['nullable', 'string', 'max:255'],
            'products_homepage_limit' => ['nullable', 'integer', 'min:0', 'max:100'],
            'industries_section_title_ar' => ['nullable', 'string', 'max:255'],
            'industries_section_title_en' => ['nullable', 'string', 'max:255'],
            'about_section_title_ar' => ['nullable', 'string', 'max:255'],
            'about_section_title_en' => ['nullable', 'string', 'max:255'],
            'about_highlight_ar' => ['nullable', 'string', 'max:255'],
            'about_highlight_en' => ['nullable', 'string', 'max:255'],
            'about_cta_text_ar' => ['nullable', 'string', 'max:255'],
            'about_cta_text_en' => ['nullable', 'string', 'max:255'],
            'about_cta_url' => ['nullable', 'string', 'max:500'],
            'gallery_section_title_ar' => ['nullable', 'string', 'max:255'],
            'gallery_section_title_en' => ['nullable', 'string', 'max:255'],
            'contact_section_title_ar' => ['nullable', 'string', 'max:255'],
            'contact_section_title_en' => ['nullable', 'string', 'max:255'],
            'contact_section_subtitle_ar' => ['nullable', 'string', 'max:1000'],
            'contact_section_subtitle_en' => ['nullable', 'string', 'max:1000'],
            'footer_description_ar' => ['nullable', 'string', 'max:5000'],
            'footer_description_en' => ['nullable', 'string', 'max:5000'],
            'footer_newsletter_title_ar' => ['nullable', 'string', 'max:255'],
            'footer_newsletter_title_en' => ['nullable', 'string', 'max:255'],
            'footer_newsletter_description_ar' => ['nullable', 'string', 'max:1000'],
            'footer_newsletter_description_en' => ['nullable', 'string', 'max:1000'],
            'footer_newsletter_button_ar' => ['nullable', 'string', 'max:255'],
            'footer_newsletter_button_en' => ['nullable', 'string', 'max:255'],
            'footer_newsletter_placeholder_ar' => ['nullable', 'string', 'max:255'],
            'footer_newsletter_placeholder_en' => ['nullable', 'string', 'max:255'],
            'footer_copyright_ar' => ['nullable', 'string', 'max:500'],
            'footer_copyright_en' => ['nullable', 'string', 'max:500'],
            'logo' => SafeRasterImage::rules(),
            'about_image' => SafeRasterImage::rules(),
        ];
    }
}
