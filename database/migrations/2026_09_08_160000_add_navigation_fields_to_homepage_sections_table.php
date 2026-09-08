<?php

use App\Support\HomepageSectionNavigation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->boolean('show_in_navigation')->default(false)->after('is_visible');
            $table->string('nav_label_ar')->nullable()->after('show_in_navigation');
            $table->string('nav_label_en')->nullable()->after('nav_label_ar');
            $table->unsignedInteger('nav_order')->default(0)->after('nav_label_en');
            $table->string('anchor_id', 120)->nullable()->after('nav_order');
        });

        foreach (HomepageSectionNavigation::defaultsByKey() as $key => $config) {
            DB::table('homepage_sections')
                ->where('key', $key)
                ->update([
                    'show_in_navigation' => $config['show_in_navigation'],
                    'nav_label_ar' => $config['nav_label_ar'],
                    'nav_label_en' => $config['nav_label_en'],
                    'nav_order' => $config['nav_order'],
                    'anchor_id' => $config['anchor_id'],
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->dropColumn([
                'show_in_navigation',
                'nav_label_ar',
                'nav_label_en',
                'nav_order',
                'anchor_id',
            ]);
        });
    }
};
