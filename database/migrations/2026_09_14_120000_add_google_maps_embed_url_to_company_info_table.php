<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            if (! Schema::hasColumn('company_info', 'google_maps_embed_url')) {
                $table->text('google_maps_embed_url')->nullable()->after('address_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_info', function (Blueprint $table) {
            if (Schema::hasColumn('company_info', 'google_maps_embed_url')) {
                $table->dropColumn('google_maps_embed_url');
            }
        });
    }
};
