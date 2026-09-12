<?php

use App\Enums\HomepagePromoType;
use App\Enums\HomepageSectionType;
use App\Models\HomepagePromoBlock;
use App\Models\HomepageSection;
use App\Support\HomepageSectionNavigation;
use App\Support\HomepageWhyUsContentDefaults;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! HomepageSection::query()->where('key', 'why_us')->exists()) {
            HomepageSection::query()
                ->where('ordering', '>=', 4)
                ->increment('ordering');
        }

        $copy = HomepageWhyUsContentDefaults::section();
        $navDefaults = HomepageSectionNavigation::defaultsByKey()['why_us'];

        HomepageSection::query()->updateOrCreate(
            ['key' => 'why_us'],
            [
                'name' => 'Why Us',
                'type' => HomepageSectionType::WhyUs,
                'ordering' => 4,
                'is_visible' => true,
                'title_ar' => $copy['title_ar'],
                'title_en' => $copy['title_en'],
                'settings' => [
                    'headline_ar' => $copy['headline_ar'],
                    'headline_en' => $copy['headline_en'],
                    'highlight_ar' => $copy['highlight_ar'],
                    'highlight_en' => $copy['highlight_en'],
                ],
                ...$navDefaults,
            ],
        );

        foreach (HomepageWhyUsContentDefaults::items() as $item) {
            $block = HomepagePromoBlock::query()
                ->where('type', HomepagePromoType::WhyUsHighlight)
                ->where(function ($query) use ($item) {
                    $query->where('ordering', $item['ordering'])
                        ->orWhereIn('title_en', $item['match_en'])
                        ->orWhereIn('title_ar', $item['match_ar']);
                })
                ->orderByRaw('CASE WHEN ordering = ? THEN 0 ELSE 1 END', [$item['ordering']])
                ->first();

            $payload = [
                'type' => HomepagePromoType::WhyUsHighlight,
                'icon' => $item['icon'],
                'title_ar' => $item['title_ar'],
                'title_en' => $item['title_en'],
                'description_ar' => $item['description_ar'],
                'description_en' => $item['description_en'],
                'ordering' => $item['ordering'],
                'is_active' => true,
            ];

            if ($block) {
                $block->update($payload);
            } else {
                HomepagePromoBlock::create($payload);
            }
        }
    }

    public function down(): void
    {
        HomepagePromoBlock::query()
            ->where('type', HomepagePromoType::WhyUsHighlight)
            ->delete();

        HomepageSection::query()->where('key', 'why_us')->delete();

        HomepageSection::query()
            ->where('ordering', '>=', 5)
            ->decrement('ordering');
    }
};
