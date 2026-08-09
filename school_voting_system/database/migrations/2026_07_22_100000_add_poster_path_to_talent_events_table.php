<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            if (! Schema::hasColumn('talent_events', 'poster_path')) {
                $table->string('poster_path')->nullable()->after('thumbnail_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            if (Schema::hasColumn('talent_events', 'poster_path')) {
                $table->dropColumn('poster_path');
            }
        });
    }
};
