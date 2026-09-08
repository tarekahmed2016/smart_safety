<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use App\Support\HomepageSectionDefaults;
use App\Support\HomepageSectionNavigation;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (HomepageSectionDefaults::sections() as $section) {
            $navDefaults = HomepageSectionNavigation::defaultsByKey()[$section['key']] ?? [
                'show_in_navigation' => false,
                'nav_label_ar' => null,
                'nav_label_en' => null,
                'nav_order' => 0,
                'anchor_id' => null,
            ];

            HomepageSection::query()->updateOrCreate(
                ['key' => $section['key']],
                [
                    'name' => $section['name'],
                    'type' => $section['type'],
                    'ordering' => $section['ordering'],
                    'is_visible' => $section['is_visible'],
                    'title_ar' => $section['title_ar'],
                    'title_en' => $section['title_en'],
                    'settings' => $section['settings'],
                    ...$navDefaults,
                ],
            );
        }
    }
}
