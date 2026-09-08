<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use App\Support\HomepageSectionDefaults;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (HomepageSectionDefaults::sections() as $section) {
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
                ],
            );
        }
    }
}
