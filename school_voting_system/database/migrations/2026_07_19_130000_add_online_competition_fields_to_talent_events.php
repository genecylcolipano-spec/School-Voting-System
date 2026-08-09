<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            $table->dateTime('registration_starts_at')->nullable()->after('voting_ends_at');
            $table->dateTime('registration_ends_at')->nullable()->after('registration_starts_at');
            $table->dateTime('submission_deadline')->nullable()->after('registration_ends_at');
            $table->unsignedSmallInteger('max_video_duration_seconds')->nullable()->default(300)->after('submission_deadline');
            $table->unsignedSmallInteger('max_upload_size_mb')->nullable()->default(100)->after('max_video_duration_seconds');
            $table->string('accepted_video_formats', 120)->default('mp4,mov,webm')->after('max_upload_size_mb');
        });

        Schema::table('talent_event_entries', function (Blueprint $table) {
            $table->string('student_id_number', 50)->nullable()->after('user_id');
            $table->string('course_strand', 120)->nullable()->after('section');
            $table->string('talent_category', 60)->nullable()->after('course_strand');
            $table->string('performance_title', 200)->nullable()->after('talent_category');
            $table->string('video_path')->nullable()->after('poster_path');
            $table->string('video_url')->nullable()->after('video_path');
            $table->string('thumbnail_path')->nullable()->after('video_url');
            $table->string('social_media')->nullable()->after('thumbnail_path');
            $table->timestamp('submitted_at')->nullable()->after('social_media');
            $table->unsignedInteger('view_count')->default(0)->after('submitted_at');
            $table->string('source', 20)->default('admin')->after('view_count');

            $table->index(['talent_event_id', 'student_id_number']);
        });
    }

    public function down(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            $table->dropColumn([
                'registration_starts_at',
                'registration_ends_at',
                'submission_deadline',
                'max_video_duration_seconds',
                'max_upload_size_mb',
                'accepted_video_formats',
            ]);
        });

        Schema::table('talent_event_entries', function (Blueprint $table) {
            $table->dropIndex(['talent_event_id', 'student_id_number']);
            $table->dropColumn([
                'student_id_number',
                'course_strand',
                'talent_category',
                'performance_title',
                'video_path',
                'video_url',
                'thumbnail_path',
                'social_media',
                'submitted_at',
                'view_count',
                'source',
            ]);
        });
    }
};
