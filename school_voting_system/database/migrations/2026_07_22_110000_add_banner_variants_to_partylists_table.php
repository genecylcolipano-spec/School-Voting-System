<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partylists', function (Blueprint $table) {
            if (! Schema::hasColumn('partylists', 'banner_variants')) {
                $table->json('banner_variants')->nullable()->after('banner_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partylists', function (Blueprint $table) {
            if (Schema::hasColumn('partylists', 'banner_variants')) {
                $table->dropColumn('banner_variants');
            }
        });
    }
};
