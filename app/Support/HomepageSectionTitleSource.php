<?php

namespace App\Support;

use App\Models\CompanyInfo;
use App\Models\HomepageSection;

class HomepageSectionTitleSource
{
    /**
     * @return array<string, array{title_ar: string, title_en: string}>
     */
    public static function legacyCompanyTitleFieldsBySectionKey(): array
    {
        return [
            'products' => [
                'title_ar' => 'products_section_title_ar',
                'title_en' => 'products_section_title_en',
            ],
            'industries' => [
                'title_ar' => 'industries_section_title_ar',
                'title_en' => 'industries_section_title_en',
            ],
            'about' => [
                'title_ar' => 'about_section_title_ar',
                'title_en' => 'about_section_title_en',
            ],
            'gallery' => [
                'title_ar' => 'gallery_section_title_ar',
                'title_en' => 'gallery_section_title_en',
            ],
            'contact' => [
                'title_ar' => 'contact_section_title_ar',
                'title_en' => 'contact_section_title_en',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function allLegacyCompanyTitleFields(): array
    {
        $fields = [];

        foreach (self::legacyCompanyTitleFieldsBySectionKey() as $mapping) {
            $fields[] = $mapping['title_ar'];
            $fields[] = $mapping['title_en'];
        }

        return $fields;
    }

    public static function copyLegacyPromoTitlesIntoHomepageSections(): void
    {
        $companyInfo = CompanyInfo::query()->first();

        if (! $companyInfo) {
            return;
        }

        foreach (self::legacyCompanyTitleFieldsBySectionKey() as $key => $fields) {
            $section = HomepageSection::query()->where('key', $key)->first();

            if (! $section) {
                continue;
            }

            $payload = [];

            if (self::isBlank($section->title_ar) && ! self::isBlank($companyInfo->{$fields['title_ar']})) {
                $payload['title_ar'] = $companyInfo->{$fields['title_ar']};
            }

            if (self::isBlank($section->title_en) && ! self::isBlank($companyInfo->{$fields['title_en']})) {
                $payload['title_en'] = $companyInfo->{$fields['title_en']};
            }

            if ($payload !== []) {
                $section->update($payload);
            }
        }

        self::copyLegacyCompanyFieldsIntoSectionSettings();
    }

    public static function copyLegacyCompanyFieldsIntoSectionSettings(): void
    {
        $companyInfo = CompanyInfo::query()->first();

        if (! $companyInfo) {
            return;
        }

        $contact = HomepageSection::query()->where('key', 'contact')->first();
        if ($contact) {
            $settings = $contact->settings ?? [];
            $payload = [];

            if (self::isBlank($settings['subtitle_ar'] ?? null) && ! self::isBlank($companyInfo->contact_section_subtitle_ar)) {
                $settings['subtitle_ar'] = $companyInfo->contact_section_subtitle_ar;
                $payload['settings'] = $settings;
            }

            if (self::isBlank($settings['subtitle_en'] ?? null) && ! self::isBlank($companyInfo->contact_section_subtitle_en)) {
                $settings['subtitle_en'] = $companyInfo->contact_section_subtitle_en;
                $payload['settings'] = $settings;
            }

            if ($payload !== []) {
                $contact->update($payload);
            }
        }

        $about = HomepageSection::query()->where('key', 'about')->first();
        if ($about) {
            $settings = $about->settings ?? [];
            $payload = [];

            if (self::isBlank($settings['highlight_ar'] ?? null) && ! self::isBlank($companyInfo->about_highlight_ar)) {
                $settings['highlight_ar'] = $companyInfo->about_highlight_ar;
                $payload['settings'] = $settings;
            }

            if (self::isBlank($settings['highlight_en'] ?? null) && ! self::isBlank($companyInfo->about_highlight_en)) {
                $settings['highlight_en'] = $companyInfo->about_highlight_en;
                $payload['settings'] = $settings;
            }

            if ($payload !== []) {
                $about->update($payload);
            }
        }
    }

    private static function isBlank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }
}
