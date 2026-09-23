<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allowed_students', function (Blueprint $table) {
            $table->string('school_year', 9)->nullable()->after('section');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('school_year', 9)->nullable()->after('section');
        });
    }

    public function down(): void
    {
        Schema::table('allowed_students', function (Blueprint $table) {
            $table->dropColumn('school_year');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('school_year');
        });
    }
};
