<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(true)->after('is_active');
        });

        Schema::table('clients_partners', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn('show_on_homepage');
        });

        Schema::table('clients_partners', function (Blueprint $table) {
            $table->dropColumn('show_on_homepage');
        });
    }
};
