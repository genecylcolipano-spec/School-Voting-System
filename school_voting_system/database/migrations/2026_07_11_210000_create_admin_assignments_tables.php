<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->nullable()->constrained()->nullOnDelete();
            $table->json('grade_levels')->nullable();
            $table->json('sections')->nullable();
            $table->json('strands')->nullable();
            $table->unsignedTinyInteger('turnout_target')->default(75);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('admin_verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->string('subject_type', 50);
            $table->unsignedBigInteger('subject_id');
            $table->string('title');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
        });

        Schema::create('admin_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('election_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
        });

        Schema::create('voter_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('blocked_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->boolean('notified_super_admin')->default(false);
            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamp('unblocked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_help_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('request_type', 50);
            $table->text('message');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_help_requests');
        Schema::dropIfExists('voter_blocks');
        Schema::dropIfExists('admin_complaints');
        Schema::dropIfExists('admin_verification_requests');
        Schema::dropIfExists('admin_assignments');
    }
};
