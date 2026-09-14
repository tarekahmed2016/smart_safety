<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients_partners', function (Blueprint $table) {
            if (! Schema::hasColumn('clients_partners', 'show_type_badge')) {
                $table->boolean('show_type_badge')->default(true)->after('show_on_homepage');
            }
        });

        Schema::table('clients_partners', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->change();
            $table->string('name_en')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clients_partners', function (Blueprint $table) {
            $table->string('name_ar')->nullable(false)->change();
            $table->string('name_en')->nullable(false)->change();
        });

        Schema::table('clients_partners', function (Blueprint $table) {
            if (Schema::hasColumn('clients_partners', 'show_type_badge')) {
                $table->dropColumn('show_type_badge');
            }
        });
    }
};
