<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partylists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('acronym', 20)->nullable();
            $table->text('platform')->nullable();
            $table->string('motto')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['election_id', 'status']);
        });

        Schema::create('partylist_posters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partylist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_reason')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['election_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partylist_posters');
        Schema::dropIfExists('partylists');
    }
};
