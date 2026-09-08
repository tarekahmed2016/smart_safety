<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->string('company_name_text_color', 7)->nullable()->after('name_en');
        });
    }

    public function down(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->dropColumn('company_name_text_color');
        });
    }
};
