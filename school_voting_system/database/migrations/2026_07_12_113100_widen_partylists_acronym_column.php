<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partylists', function (Blueprint $table) {
            $table->string('acronym', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('partylists', function (Blueprint $table) {
            $table->string('acronym', 20)->nullable()->change();
        });
    }
};
