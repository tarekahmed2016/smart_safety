<?php

use App\Enums\HomepageSectionType;
use App\Models\HomepageSection;
use App\Support\HomepageSectionNavigation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! HomepageSection::query()->where('key', 'vision_mission')->exists()) {
            HomepageSection::query()
                ->where('ordering', '>=', 8)
                ->increment('ordering');
        }

        $navDefaults = HomepageSectionNavigation::defaultsByKey()['vision_mission'] ?? [
            'show_in_navigation' => true,
            'nav_label_ar' => 'رؤيتنا ورسالتنا',
            'nav_label_en' => 'Vision & Mission',
            'nav_order' => 21,
            'anchor_id' => 'vision-mission',
        ];

        HomepageSection::query()->updateOrCreate(
            ['key' => 'vision_mission'],
            [
                'name' => 'Vision & Mission',
                'type' => HomepageSectionType::VisionMission,
                'ordering' => 8,
                'is_visible' => true,
                'title_ar' => 'رؤيتنا ورسالتنا',
                'title_en' => 'Vision & Mission',
                'settings' => [
                    'headline_ar' => 'نحو صناعة متكاملة',
                    'headline_en' => 'Towards an integrated industry',
                ],
                ...$navDefaults,
            ],
        );
    }

    public function down(): void
    {
        HomepageSection::query()->where('key', 'vision_mission')->delete();

        HomepageSection::query()
            ->where('ordering', '>=', 9)
            ->decrement('ordering');
    }
};
