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
    }

    private static function isBlank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }
}
