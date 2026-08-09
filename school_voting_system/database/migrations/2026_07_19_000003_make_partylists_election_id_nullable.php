<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('partylists', 'election_id')) {
            return;
        }

        // Campaigns become independent of a single election; the pivot table is
        // the source of truth. Keep the column nullable for legacy reads.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE partylists MODIFY election_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('partylists', function (Blueprint $table) {
                $table->unsignedBigInteger('election_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('partylists', 'election_id')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE partylists MODIFY election_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('partylists', function (Blueprint $table) {
                $table->unsignedBigInteger('election_id')->nullable(false)->change();
            });
        }
    }
};
