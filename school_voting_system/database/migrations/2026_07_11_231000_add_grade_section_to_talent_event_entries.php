<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_event_entries', function (Blueprint $table) {
            $table->string('grade_level', 10)->nullable()->after('display_name');
            $table->string('section', 20)->nullable()->after('grade_level');
        });
    }

    public function down(): void
    {
        Schema::table('talent_event_entries', function (Blueprint $table) {
            $table->dropColumn(['grade_level', 'section']);
        });
    }
};
