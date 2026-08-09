<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->timestamp('results_published_at')->nullable()->after('public_results_published');
            $table->foreignId('results_published_by')->nullable()->after('results_published_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('talent_events', function (Blueprint $table) {
            $table->foreignId('results_published_by')->nullable()->after('results_published_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('results_published_by');
            $table->dropColumn('results_published_at');
        });

        Schema::table('talent_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('results_published_by');
        });
    }
};
