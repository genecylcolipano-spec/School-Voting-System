<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            $table->boolean('published_to_students')->default(false)->after('results_published_at');
            $table->timestamp('published_at')->nullable()->after('published_to_students');
        });

        Schema::table('talent_event_entries', function (Blueprint $table) {
            $table->string('poster_path')->nullable()->after('photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('talent_event_entries', function (Blueprint $table) {
            $table->dropColumn('poster_path');
        });

        Schema::table('talent_events', function (Blueprint $table) {
            $table->dropColumn(['published_to_students', 'published_at']);
        });
    }
};
