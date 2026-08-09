<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            $table->string('talent_category', 60)->nullable()->after('type');
            $table->unsignedSmallInteger('max_performance_duration_minutes')->default(5)->after('venue');
            $table->unsignedSmallInteger('max_contestants')->nullable()->after('max_performance_duration_minutes');
            $table->string('voting_method', 40)->default('student_only')->after('max_contestants');
            $table->unsignedTinyInteger('judge_percentage')->nullable()->after('voting_method');
            $table->unsignedTinyInteger('student_vote_percentage')->nullable()->after('judge_percentage');
            $table->unsignedSmallInteger('number_of_winners')->default(3)->after('student_vote_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            $table->dropColumn([
                'talent_category',
                'max_performance_duration_minutes',
                'max_contestants',
                'voting_method',
                'judge_percentage',
                'student_vote_percentage',
                'number_of_winners',
            ]);
        });
    }
};
