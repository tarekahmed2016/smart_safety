<?php

use App\Models\HomepageSection;
use App\Models\Page;
use App\Support\HomepageSectionNavigation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (HomepageSectionNavigation::defaultsByKey() as $key => $config) {
            $payload = [
                'show_in_navigation' => $config['show_in_navigation'],
                'nav_label_ar' => $config['nav_label_ar'],
                'nav_label_en' => $config['nav_label_en'],
                'nav_order' => $config['nav_order'],
                'anchor_id' => $config['anchor_id'],
            ];

            if ($key === 'services') {
                $payload['is_visible'] = true;
            }

            HomepageSection::query()->where('key', $key)->update($payload);
        }

        Page::query()
            ->where('slug', 'goals')
            ->update(['show_in_main_menu' => false]);
    }

    public function down(): void
    {
        HomepageSection::query()->where('key', 'gallery')->update([
            'show_in_navigation' => true,
            'nav_order' => 50,
        ]);

        HomepageSection::query()->where('key', 'contact')->update([
            'anchor_id' => 'contact',
            'nav_order' => 100,
        ]);
    }
};
