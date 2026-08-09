<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            if (! Schema::hasColumn('talent_events', 'image_variants')) {
                $table->json('image_variants')->nullable()->after('image_path');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'image_variants')) {
                $table->json('image_variants')->nullable()->after('image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            if (Schema::hasColumn('talent_events', 'image_variants')) {
                $table->dropColumn('image_variants');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'image_variants')) {
                $table->dropColumn('image_variants');
            }
        });
    }
};
