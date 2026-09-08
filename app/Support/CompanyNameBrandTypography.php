<?php

namespace App\Support;

class CompanyNameBrandTypography
{
    public const DEFAULT_FONT_FAMILY_AR = "'Cairo', 'Tajawal', Arial, sans-serif";

    public const DEFAULT_FONT_FAMILY_EN = "'Poppins', Arial, sans-serif";

    public const DEFAULT_FONT_SIZE_AR = 0.98;

    public const DEFAULT_FONT_SIZE_EN = 0.74;

    public const DEFAULT_FONT_WEIGHT = 800;

    public const DEFAULT_TEXT_COLOR = '#0B1F3A';

    /**
     * @return list<int>
     */
    public static function allowedFontWeights(): array
    {
        return [400, 500, 600, 700, 800, 900];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'company_name_font_family_ar' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\pN\s,\'"\-_\.]+$/u'],
            'company_name_font_family_en' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\pN\s,\'"\-_\.]+$/u'],
            'company_name_font_size_ar' => ['nullable', 'numeric', 'min:0.5', 'max:3'],
            'company_name_font_size_en' => ['nullable', 'numeric', 'min:0.5', 'max:3'],
            'company_name_font_weight' => ['nullable', 'integer', 'in:'.implode(',', self::allowedFontWeights())],
            'company_name_text_color' => ThemeColor::rules(),
        ];
    }

    /**
     * @return array{
     *     font_family_ar: string,
     *     font_family_en: string,
     *     font_size_ar: float,
     *     font_size_en: float,
     *     font_weight: int,
     *     text_color: string
     * }
     */
    public static function resolvedFor(object $companyInfo): array
    {
        return [
            'font_family_ar' => self::normalizeFontFamily($companyInfo->company_name_font_family_ar ?? null, self::DEFAULT_FONT_FAMILY_AR),
            'font_family_en' => self::normalizeFontFamily($companyInfo->company_name_font_family_en ?? null, self::DEFAULT_FONT_FAMILY_EN),
            'font_size_ar' => self::normalizeFontSize($companyInfo->company_name_font_size_ar ?? null, self::DEFAULT_FONT_SIZE_AR),
            'font_size_en' => self::normalizeFontSize($companyInfo->company_name_font_size_en ?? null, self::DEFAULT_FONT_SIZE_EN),
            'font_weight' => self::normalizeFontWeight($companyInfo->company_name_font_weight ?? null),
            'text_color' => ThemeColor::normalize($companyInfo->company_name_text_color ?? null) ?? self::DEFAULT_TEXT_COLOR,
        ];
    }

    public static function normalizeFontSize(mixed $value, float $default): float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return round((float) $value, 2);
    }

    public static function normalizeFontWeight(mixed $value): int
    {
        $weight = (int) ($value ?: self::DEFAULT_FONT_WEIGHT);

        return in_array($weight, self::allowedFontWeights(), true)
            ? $weight
            : self::DEFAULT_FONT_WEIGHT;
    }

    public static function normalizeFontFamily(mixed $value, string $default): string
    {
        $family = trim((string) ($value ?? ''));

        return $family !== '' ? $family : $default;
    }
}
