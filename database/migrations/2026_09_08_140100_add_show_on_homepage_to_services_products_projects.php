<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(true)->after('is_active');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(true)->after('is_active');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('show_on_homepage');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('show_on_homepage');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('show_on_homepage');
        });
    }
};
