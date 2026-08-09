<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Positions within an election (e.g. President, Vice President).
     * Enables one vote per category via votes.election_category_id unique constraint.
     */
    public function up(): void
    {
        Schema::create('election_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('max_selections')->default(1);
            $table->timestamps();

            $table->unique(['election_id', 'slug']);
            $table->index(['election_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_categories');
    }
};
