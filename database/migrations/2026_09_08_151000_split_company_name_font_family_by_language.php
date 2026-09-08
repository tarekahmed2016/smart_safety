<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->string('company_name_font_family_ar', 255)->nullable()->after('company_name_text_color');
            $table->string('company_name_font_family_en', 255)->nullable()->after('company_name_font_family_ar');
        });

        if (Schema::hasColumn('company_info', 'company_name_font_family')) {
            DB::table('company_info')
                ->whereNotNull('company_name_font_family')
                ->where('company_name_font_family', '!=', '')
                ->update([
                    'company_name_font_family_ar' => DB::raw('company_name_font_family'),
                    'company_name_font_family_en' => DB::raw('company_name_font_family'),
                ]);

            Schema::table('company_info', function (Blueprint $table) {
                $table->dropColumn('company_name_font_family');
            });
        }
    }

    public function down(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->string('company_name_font_family', 255)->nullable()->after('company_name_text_color');
        });

        DB::table('company_info')
            ->where(function ($query) {
                $query->whereNotNull('company_name_font_family_ar')
                    ->orWhereNotNull('company_name_font_family_en');
            })
            ->update([
                'company_name_font_family' => DB::raw('COALESCE(company_name_font_family_ar, company_name_font_family_en)'),
            ]);

        Schema::table('company_info', function (Blueprint $table) {
            $table->dropColumn([
                'company_name_font_family_ar',
                'company_name_font_family_en',
            ]);
        });
    }
};
