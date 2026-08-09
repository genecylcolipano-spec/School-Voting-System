<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamp('voting_starts_at')->nullable();
            $table->timestamp('voting_ends_at')->nullable();
            $table->enum('status', ['draft', 'active', 'closed', 'archived'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'voting_starts_at', 'voting_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
