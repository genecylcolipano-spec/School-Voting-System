<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_event_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('talent_event_entries', 'entry_number')) {
                $table->string('entry_number', 40)->nullable()->after('id');
                $table->unique('entry_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('talent_event_entries', function (Blueprint $table) {
            if (Schema::hasColumn('talent_event_entries', 'entry_number')) {
                $table->dropUnique(['entry_number']);
                $table->dropColumn('entry_number');
            }
        });
    }
};
