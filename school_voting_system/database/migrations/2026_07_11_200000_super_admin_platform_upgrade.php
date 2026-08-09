<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('category')->default('general');
            $table->timestamps();
        });

        Schema::create('staff_role_permission', function (Blueprint $table) {
            $table->foreignId('staff_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['staff_role_id', 'permission_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('staff_role_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->string('grade_level', 50)->nullable()->after('staff_role_id');
            $table->string('section', 50)->nullable()->after('grade_level');
            $table->string('student_status', 30)->default('enrolled')->after('section');
            $table->boolean('is_active')->default(true)->after('student_status');
        });

        Schema::table('passkeys', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('device_name');
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->timestamp('revoked_at')->nullable()->after('expires_at');
            $table->foreignId('revoked_by')->nullable()->after('revoked_at')->constrained('users')->nullOnDelete();
            $table->foreignId('reassigned_to_user_id')->nullable()->after('revoked_by')->constrained('users')->nullOnDelete();
            $table->timestamp('marked_lost_at')->nullable()->after('reassigned_to_user_id');
        });

        Schema::table('elections', function (Blueprint $table) {
            $table->boolean('is_paused')->default(false)->after('status');
            $table->boolean('results_locked')->default(false)->after('is_paused');
            $table->string('integrity_hash', 128)->nullable()->after('results_locked');
            $table->boolean('public_results_published')->default(false)->after('integrity_hash');
            $table->timestamp('annulled_at')->nullable()->after('public_results_published');
            $table->foreignId('rerun_parent_id')->nullable()->after('annulled_at')->constrained('elections')->nullOnDelete();
            $table->timestamp('scheduled_open_at')->nullable()->after('rerun_parent_id');
            $table->timestamp('scheduled_close_at')->nullable()->after('scheduled_open_at');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->string('position')->nullable()->after('display_name');
            $table->string('eligibility_status', 30)->default('eligible')->after('platform');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admin_name');
            $table->string('admin_role')->nullable();
            $table->string('action');
            $table->string('action_type', 50)->default('general');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_name')->nullable();
            $table->string('status', 20)->default('success');
            $table->timestamps();

            $table->index(['action_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('type', 50);
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 20)->default('completed');
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('system_backups');
        Schema::dropIfExists('audit_logs');

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['position', 'eligibility_status']);
        });

        Schema::table('elections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rerun_parent_id');
            $table->dropColumn([
                'is_paused', 'results_locked', 'integrity_hash',
                'public_results_published', 'annulled_at',
                'scheduled_open_at', 'scheduled_close_at',
            ]);
        });

        Schema::table('passkeys', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reassigned_to_user_id');
            $table->dropConstrainedForeignId('revoked_by');
            $table->dropColumn(['status', 'expires_at', 'revoked_at', 'marked_lost_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('staff_role_id');
            $table->dropColumn(['grade_level', 'section', 'student_status', 'is_active']);
        });

        Schema::dropIfExists('staff_role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('staff_roles');
    }
};
