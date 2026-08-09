<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ballot_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_token', 32)->unique();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['user_id', 'election_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ballot_submissions');
    }
};
