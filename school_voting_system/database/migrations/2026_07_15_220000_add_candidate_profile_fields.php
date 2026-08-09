<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->text('biography')->nullable()->after('platform');
            $table->text('campaign_promises')->nullable()->after('biography');
            $table->string('grade_level', 20)->nullable()->after('campaign_promises');
            $table->string('section', 50)->nullable()->after('grade_level');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['biography', 'campaign_promises', 'grade_level', 'section']);
        });
    }
};
