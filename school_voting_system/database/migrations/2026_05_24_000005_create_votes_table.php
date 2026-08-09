<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->timestamp('voted_at')->useCurrent();
            $table->timestamps();

            // One ballot line per student per position/category in an election.
            $table->unique(['user_id', 'election_category_id'], 'votes_user_category_unique');

            $table->index(['election_id', 'candidate_id']);
            $table->index(['candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
