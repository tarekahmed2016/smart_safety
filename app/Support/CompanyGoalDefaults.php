<?php

namespace App\Support;

use App\Models\Page;

class CompanyGoalDefaults
{
    /**
     * @return list<array{text_ar: string, text_en: string}>
     */
    public static function items(): array
    {
        return [
            [
                'text_ar' => 'نساهم في زرع الثقة في المنتج العماني ومنحه المجال للمنافسة في الأسواق العالمية',
                'text_en' => 'Building trust in Omani products and helping them compete in global markets.',
            ],
            [
                'text_ar' => 'المساهمة في التحول بوتيرة أسرع نحو الثورة الصناعية الرابعة في السلطنة',
                'text_en' => 'Accelerating the transition toward the Fourth Industrial Revolution in the Sultanate.',
            ],
            [
                'text_ar' => 'المساهمة في وصول السلطنة إلى مصاف الدول المتقدمة وتحفيز التقدم في الصناعات التحويلية',
                'text_en' => 'Supporting Oman’s progress toward advanced economies and transformative manufacturing industries.',
            ],
            [
                'text_ar' => 'بناء وتطوير المصانع بأحدث التقنيات وبأعلى معايير الجودة العالمية',
                'text_en' => 'Building and developing factories with modern technologies and global quality standards.',
            ],
            [
                'text_ar' => 'تقديم الاستشارات الصناعية التي تسهم في تطوير المجال الصناعي في السلطنة',
                'text_en' => 'Providing industrial consulting that supports Oman’s industrial sector.',
            ],
        ];
    }

    /**
     * Prefer English copy already stored on the legacy /page/goals CMS page.
     *
     * @return list<array{text_ar: string, text_en: string}>
     */
    public static function itemsWithCmsEnglish(?Page $goalsPage = null): array
    {
        $items = self::items();
        $page = $goalsPage ?? Page::query()->where('slug', 'goals')->first();
        $englishItems = self::extractListItems((string) ($page?->content_en ?? ''));

        foreach ($items as $index => $item) {
            if (! empty($englishItems[$index])) {
                $items[$index]['text_en'] = $englishItems[$index];
            }
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    public static function extractListItems(string $html): array
    {
        if ($html === '') {
            return [];
        }

        preg_match_all('/<li\b[^>]*>(.*?)<\/li>/isu', $html, $matches);

        return array_values(array_filter(array_map(
            fn (string $item) => trim(html_entity_decode(strip_tags($item), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            $matches[1] ?? [],
        )));
    }
}
