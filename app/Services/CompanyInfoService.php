<?php

namespace App\Services;

use App\Models\CompanyInfo;
use App\Support\HomepageAboutContentDefaults;
use App\Support\HomepageContentDefaults;
use App\Support\ThemeColor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CompanyInfoService
{
    /**
     * @var list<string>
     */
    private const COMPANY_INFO_FIELDS = [
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
    ];

    /**
     * @var list<string>
     */
    private const THEME_FIELDS = [
        'theme_primary_color',
        'theme_dark_color',
        'theme_heading_text_color',
        'theme_body_text_color',
        'theme_muted_text_color',
        'theme_nav_text_color',
        'theme_nav_hover_text_color',
        'theme_hero_text_color',
        'theme_on_dark_text_color',
    ];

    /**
     * @var list<string>
     */
    private const CUSTOM_ASSET_FIELDS = [
        'custom_css',
        'custom_js',
    ];

    /**
     * @var list<string>
     */
    private const STRING_FIELDS = [
        ...self::COMPANY_INFO_FIELDS,
        ...self::THEME_FIELDS,
        ...self::CUSTOM_ASSET_FIELDS,
    ];

    public function __construct(public ActivityLogService $activityLogService) {}

    public function getCompanyInfo(): CompanyInfo
    {
        $companyInfo = CompanyInfo::with(['attachment', 'aboutAttachment'])->first();

        if (! $companyInfo) {
            return new CompanyInfo($this->emptyDefaults());
        }

        $companyInfo->fill($this->normalizeStringFields($companyInfo));

        return $companyInfo;
    }

    /**
     * @return array<string, string>
     */
    public function getThemeColors(): array
    {
        $companyInfo = CompanyInfo::first() ?? new CompanyInfo($this->emptyDefaults());

        return ThemeColor::resolvedFor($companyInfo);
    }

    /**
     * @return array<string, string>
     */
    public function getCustomAssets(): array
    {
        $companyInfo = CompanyInfo::first();

        return [
            'custom_css' => $companyInfo?->custom_css ?? '',
            'custom_js' => $companyInfo?->custom_js ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(?CompanyInfo $companyInfo, array $data, ?UploadedFile $logo = null, ?UploadedFile $aboutImage = null): CompanyInfo
    {
        if (array_key_exists('products_homepage_limit', $data)) {
            $data['products_homepage_limit'] = max(0, (int) $data['products_homepage_limit']);
        }

        $companyInfo = $this->persist(
            companyInfo: $companyInfo,
            data: $data,
            allowedFields: self::COMPANY_INFO_FIELDS,
            logo: $logo,
        );

        if ($aboutImage) {
            $this->deleteAboutImage(companyInfo: $companyInfo);
            $this->storeAboutImage(companyInfo: $companyInfo, image: $aboutImage);
        }

        return $companyInfo;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateThemeColors(?CompanyInfo $companyInfo, array $data): CompanyInfo
    {
        return $this->persist(
            companyInfo: $companyInfo,
            data: $data,
            allowedFields: self::THEME_FIELDS,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCustomAssets(?CompanyInfo $companyInfo, array $data): CompanyInfo
    {
        return $this->persist(
            companyInfo: $companyInfo,
            data: $data,
            allowedFields: self::CUSTOM_ASSET_FIELDS,
        );
    }

    /**
     * @return array<string, string>
     */
    public function emptyDefaults(): array
    {
        return [
            'name_ar' => 'الصناعة الإبداعية',
            'name_en' => 'Creative Industry',
            'hero_title_ar' => "حلول بلاستيكية\nتصنع مستقبل أفضل",
            'hero_title_en' => "Plastic solutions\nthat build a better future",
            'hero_description_ar' => 'نختص في تصنيع المنتجات البلاستيكية وفق متطلبات العملاء، بمعايير جودة عالية وإنتاج مرن يلبي احتياجات القطاعات المختلفة.',
            'hero_description_en' => 'We specialize in manufacturing plastic products according to customer requirements, with high quality standards and flexible production for diverse sectors.',
            'about_ar' => HomepageAboutContentDefaults::companyFields()['about_ar'],
            'about_en' => HomepageAboutContentDefaults::companyFields()['about_en'],
            ...HomepageContentDefaults::companyInfoFields(),
            ...array_fill_keys(array_diff(self::STRING_FIELDS, ThemeColor::fieldNames(), [
                'name_ar', 'name_en', 'hero_title_ar', 'hero_title_en',
                'hero_description_ar', 'hero_description_en', 'about_ar', 'about_en',
                ...array_keys(HomepageContentDefaults::companyInfoFields()),
            ]), ''),
            ...ThemeColor::DEFAULTS,
        ];
    }

    /**
     * @param  list<string>  $allowedFields
     * @param  array<string, mixed>  $data
     */
    private function persist(?CompanyInfo $companyInfo, array $data, array $allowedFields, ?UploadedFile $logo = null): CompanyInfo
    {
        if (! $companyInfo?->exists) {
            $companyInfo = CompanyInfo::create($data);

            if ($logo) {
                $this->storeLogo(companyInfo: $companyInfo, logo: $logo);
            }

            $this->activityLogService->recordCreated(
                subject: $companyInfo,
                allowedFields: $allowedFields,
                subjectLabel: $this->subjectLabel($companyInfo),
            );

            return $companyInfo;
        }

        $originalValues = $companyInfo->only($allowedFields);

        $companyInfo->update($data);

        if ($logo) {
            $this->deleteLogo(companyInfo: $companyInfo);
            $this->storeLogo(companyInfo: $companyInfo, logo: $logo);
        }

        $this->activityLogService->recordChanges(
            subject: $companyInfo,
            originalValues: $originalValues,
            allowedFields: $allowedFields,
            subjectLabel: $this->subjectLabel($companyInfo),
        );

        return $companyInfo;
    }

    /**
     * @return array<string, string>
     */
    private function normalizeStringFields(CompanyInfo $companyInfo): array
    {
        $resolvedTheme = ThemeColor::resolvedFor($companyInfo);
        $normalized = [];

        foreach (self::STRING_FIELDS as $field) {
            if (in_array($field, self::CUSTOM_ASSET_FIELDS, true)) {
                $normalized[$field] = $companyInfo->{$field} ?? '';

                continue;
            }

            $normalized[$field] = $resolvedTheme[$field] ?? ($companyInfo->{$field} ?? '');
        }

        return $normalized;
    }

    private function subjectLabel(CompanyInfo $companyInfo): string
    {
        return $companyInfo->name_ar ?: $companyInfo->name_en ?: 'Company Info';
    }

    private function storeLogo(CompanyInfo $companyInfo, UploadedFile $logo): void
    {
        $path = $logo->store('company-info', 'public');
        $companyInfo->attachment()->create([
            'name' => $logo->getClientOriginalName(),
            'path' => $path,
        ]);
    }

    private function deleteLogo(CompanyInfo $companyInfo): void
    {
        $attachment = $companyInfo->attachment;
        if ($attachment && $attachment->path && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        if ($attachment) {
            $attachment->delete();
        }
    }

    private function storeAboutImage(CompanyInfo $companyInfo, UploadedFile $image): void
    {
        $path = $image->store('company-info/about', 'public');
        $companyInfo->aboutAttachment()->create([
            'name' => $image->getClientOriginalName(),
            'path' => $path,
            'collection' => 'about',
        ]);
    }

    private function deleteAboutImage(CompanyInfo $companyInfo): void
    {
        $attachment = $companyInfo->aboutAttachment;
        if ($attachment && $attachment->path && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        if ($attachment) {
            $attachment->delete();
        }
    }
}
