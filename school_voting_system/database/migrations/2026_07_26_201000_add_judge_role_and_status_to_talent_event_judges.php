<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_event_judges', function (Blueprint $table) {
            if (! Schema::hasColumn('talent_event_judges', 'judge_role')) {
                $table->string('judge_role', 30)->default('judge')->after('assigned_by');
            }
            if (! Schema::hasColumn('talent_event_judges', 'status')) {
                $table->string('status', 20)->default('active')->after('judge_role');
            }
            if (! Schema::hasColumn('talent_event_judges', 'removal_reason')) {
                $table->text('removal_reason')->nullable()->after('status');
            }
            if (! Schema::hasColumn('talent_event_judges', 'removed_at')) {
                $table->timestamp('removed_at')->nullable()->after('removal_reason');
            }
            if (! Schema::hasColumn('talent_event_judges', 'removed_by')) {
                $table->foreignId('removed_by')->nullable()->after('removed_at')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('talent_event_judges', function (Blueprint $table) {
            $table->index(['talent_event_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('talent_event_judges', function (Blueprint $table) {
            if (Schema::hasColumn('talent_event_judges', 'removed_by')) {
                $table->dropConstrainedForeignId('removed_by');
            }
            foreach (['removed_at', 'removal_reason', 'status', 'judge_role'] as $column) {
                if (Schema::hasColumn('talent_event_judges', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
