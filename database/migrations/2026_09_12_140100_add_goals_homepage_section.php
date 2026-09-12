<?php

use App\Enums\HomepageSectionType;
use App\Models\HomepageSection;
use App\Support\HomepageSectionNavigation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! HomepageSection::query()->where('key', 'goals')->exists()) {
            HomepageSection::query()
                ->where('ordering', '>=', 8)
                ->increment('ordering');
        }

        $navDefaults = HomepageSectionNavigation::defaultsByKey()['goals'] ?? [
            'show_in_navigation' => false,
            'nav_label_ar' => 'أهدافنا',
            'nav_label_en' => 'Our Goals',
            'nav_order' => 22,
            'anchor_id' => 'goals',
        ];

        HomepageSection::query()->updateOrCreate(
            ['key' => 'goals'],
            [
                'name' => 'Goals',
                'type' => HomepageSectionType::Goals,
                'ordering' => 8,
                'is_visible' => true,
                'title_ar' => 'أهدافنا',
                'title_en' => 'Our Goals',
                'settings' => [
                    'headline_ar' => 'نسعى لتحقيق التميز',
                    'headline_en' => 'We strive for excellence',
                ],
                ...$navDefaults,
            ],
        );
    }

    public function down(): void
    {
        HomepageSection::query()->where('key', 'goals')->delete();

        HomepageSection::query()
            ->where('ordering', '>=', 9)
            ->decrement('ordering');
    }
};
