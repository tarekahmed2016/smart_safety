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
        Schema::create('visitor_events', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_hash', 64);
            $table->string('session_hash', 64);
            $table->date('visited_on');
            $table->timestamp('last_seen_at');
            $table->string('path', 80)->nullable();
            $table->string('event_type', 20);
            $table->timestamps();

            $table->index(['visited_on', 'event_type']);
            $table->index(['visitor_hash', 'visited_on', 'event_type']);
            $table->index(['session_hash', 'visited_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_events');
    }
};
