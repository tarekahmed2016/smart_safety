<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->longText('details_ar')->nullable()->after('description_en');
            $table->longText('details_en')->nullable()->after('details_ar');
            $table->json('sizes')->nullable()->after('details_en');
            $table->json('specifications_ar')->nullable()->after('sizes');
            $table->json('specifications_en')->nullable()->after('specifications_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'details_ar',
                'details_en',
                'sizes',
                'specifications_ar',
                'specifications_en',
            ]);
        });
    }
};
