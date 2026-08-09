<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            $table->string('competition_code', 50)->nullable()->after('slug');
            $table->string('organizer')->nullable()->after('venue');
            $table->string('thumbnail_path')->nullable()->after('image_path');
            $table->timestamp('results_publish_at')->nullable()->after('voting_ends_at');
            $table->string('registration_method', 20)->default('both')->after('submission_deadline');
            $table->string('submission_method', 20)->default('both')->after('registration_method');
            $table->string('ranking_method', 20)->default('votes')->after('number_of_winners');
            $table->boolean('is_paused')->default(false)->after('status');
            $table->boolean('auto_status_updates')->default(true)->after('is_paused');
            $table->softDeletes();

            $table->index('competition_code');
            $table->index('registration_method');
        });
    }

    public function down(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            $table->dropIndex(['competition_code']);
            $table->dropIndex(['registration_method']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'competition_code',
                'organizer',
                'thumbnail_path',
                'results_publish_at',
                'registration_method',
                'submission_method',
                'ranking_method',
                'is_paused',
                'auto_status_updates',
            ]);
        });
    }
};
