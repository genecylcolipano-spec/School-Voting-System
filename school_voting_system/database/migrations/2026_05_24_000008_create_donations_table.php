<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundraiser_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PHP');
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('donated_at')->useCurrent();
            $table->timestamps();

            $table->index(['fundraiser_id', 'donated_at']);
            $table->index(['user_id', 'fundraiser_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
