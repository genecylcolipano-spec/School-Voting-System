<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('talent_event_judges')) {
            Schema::create('talent_event_judges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('talent_event_id')->constrained('talent_events')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->useCurrent();
                $table->timestamps();

                $table->unique(['talent_event_id', 'user_id']);
                $table->index(['user_id', 'talent_event_id']);
            });
        }

        if (! Schema::hasTable('talent_judging_criteria')) {
            Schema::create('talent_judging_criteria', function (Blueprint $table) {
                $table->id();
                $table->foreignId('talent_event_id')->constrained('talent_events')->cascadeOnDelete();
                $table->string('name');
                $table->unsignedTinyInteger('max_points')->default(25);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['talent_event_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('talent_judge_score_sheets')) {
            Schema::create('talent_judge_score_sheets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('talent_event_id')->constrained('talent_events')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('talent_event_entry_id')->constrained('talent_event_entries')->cascadeOnDelete();
                $table->decimal('total_score', 8, 2)->default(0);
                $table->string('status', 20)->default('draft');
                $table->text('notes')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['talent_event_id', 'user_id', 'talent_event_entry_id'], 'talent_judge_sheets_unique');
                $table->index(['user_id', 'status']);
            });
        }

        if (! Schema::hasTable('talent_judge_criterion_scores')) {
            Schema::create('talent_judge_criterion_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('score_sheet_id')->constrained('talent_judge_score_sheets')->cascadeOnDelete();
                $table->foreignId('criterion_id')->constrained('talent_judging_criteria')->cascadeOnDelete();
                $table->decimal('points', 8, 2)->default(0);
                $table->timestamps();

                $table->unique(['score_sheet_id', 'criterion_id'], 'talent_judge_criterion_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_judge_criterion_scores');
        Schema::dropIfExists('talent_judge_score_sheets');
        Schema::dropIfExists('talent_judging_criteria');
        Schema::dropIfExists('talent_event_judges');
    }
};
