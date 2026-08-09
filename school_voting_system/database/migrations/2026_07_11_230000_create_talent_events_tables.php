<?php

use App\Enums\TalentEventStatus;
use App\Enums\TalentEventType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 30)->default(TalentEventType::TalentCompetition->value);
            $table->text('description')->nullable();
            $table->dateTime('event_date');
            $table->string('venue')->nullable();
            $table->string('status', 30)->default(TalentEventStatus::Scheduled->value);
            $table->dateTime('voting_starts_at')->nullable();
            $table->dateTime('voting_ends_at')->nullable();
            $table->timestamp('results_published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['election_id', 'status']);
        });

        Schema::create('talent_event_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name');
            $table->text('profile_summary')->nullable();
            $table->text('performance_description')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['talent_event_id', 'status']);
        });

        Schema::create('talent_event_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talent_event_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('voted_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'talent_event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_event_votes');
        Schema::dropIfExists('talent_event_entries');
        Schema::dropIfExists('talent_events');
    }
};
