<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_event_entry_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talent_event_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('watched_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'talent_event_entry_id'], 'talent_entry_views_user_entry_unique');
            $table->index(['talent_event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_event_entry_views');
    }
};
