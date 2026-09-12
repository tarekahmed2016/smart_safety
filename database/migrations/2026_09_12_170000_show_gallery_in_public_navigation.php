<?php

use App\Models\HomepageSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        HomepageSection::query()->where('key', 'gallery')->update([
            'show_in_navigation' => true,
            'nav_label_ar' => 'معرض الصور',
            'nav_label_en' => 'Gallery',
            'nav_order' => 80,
            'anchor_id' => 'gallery',
        ]);

        HomepageSection::query()->where('key', 'custom_manufacturing')->update([
            'nav_order' => 90,
        ]);

        HomepageSection::query()->where('key', 'contact')->update([
            'nav_order' => 100,
        ]);
    }

    public function down(): void
    {
        HomepageSection::query()->where('key', 'gallery')->update([
            'show_in_navigation' => false,
            'nav_order' => 100,
        ]);

        HomepageSection::query()->where('key', 'custom_manufacturing')->update([
            'nav_order' => 80,
        ]);

        HomepageSection::query()->where('key', 'contact')->update([
            'nav_order' => 90,
        ]);
    }
};
