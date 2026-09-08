<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->string('company_name_font_family', 255)->nullable()->after('company_name_text_color');
            $table->decimal('company_name_font_size_ar', 4, 2)->nullable()->after('company_name_font_family');
            $table->decimal('company_name_font_size_en', 4, 2)->nullable()->after('company_name_font_size_ar');
            $table->unsignedSmallInteger('company_name_font_weight')->nullable()->after('company_name_font_size_en');
        });
    }

    public function down(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->dropColumn([
                'company_name_font_family',
                'company_name_font_size_ar',
                'company_name_font_size_en',
                'company_name_font_weight',
            ]);
        });
    }
};
