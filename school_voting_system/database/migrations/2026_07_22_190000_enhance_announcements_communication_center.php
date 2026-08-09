<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'category')) {
                $table->string('category')->default('general')->after('body');
            }
            if (! Schema::hasColumn('announcements', 'priority')) {
                $table->string('priority')->default('normal')->after('category');
            }
            if (! Schema::hasColumn('announcements', 'target_audiences')) {
                $table->json('target_audiences')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('announcements', 'target_grade_level')) {
                $table->string('target_grade_level')->nullable()->after('target_audiences');
            }
            if (! Schema::hasColumn('announcements', 'target_section')) {
                $table->string('target_section')->nullable()->after('target_grade_level');
            }
            if (! Schema::hasColumn('announcements', 'related_module')) {
                $table->string('related_module')->default('none')->after('target_section');
            }
            if (! Schema::hasColumn('announcements', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable()->after('related_module');
            }
            if (! Schema::hasColumn('announcements', 'banner_path')) {
                $table->string('banner_path')->nullable()->after('related_id');
            }
            if (! Schema::hasColumn('announcements', 'banner_variants')) {
                $table->json('banner_variants')->nullable()->after('banner_path');
            }
            if (! Schema::hasColumn('announcements', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('published_at');
            }
            if (! Schema::hasColumn('announcements', 'status')) {
                $table->string('status', 32)->default('draft')->after('is_published');
            }
            if (! Schema::hasColumn('announcements', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('status');
            }
            if (! Schema::hasColumn('announcements', 'notify_in_app')) {
                $table->boolean('notify_in_app')->default(true)->after('is_pinned');
            }
            if (! Schema::hasColumn('announcements', 'show_on_dashboard')) {
                $table->boolean('show_on_dashboard')->default(true)->after('notify_in_app');
            }
            if (! Schema::hasColumn('announcements', 'pin_to_homepage')) {
                $table->boolean('pin_to_homepage')->default(false)->after('show_on_dashboard');
            }
            if (! Schema::hasColumn('announcements', 'send_email')) {
                $table->boolean('send_email')->default(false)->after('pin_to_homepage');
            }
            if (! Schema::hasColumn('announcements', 'is_auto_generated')) {
                $table->boolean('is_auto_generated')->default(false)->after('send_email');
            }
            if (! Schema::hasColumn('announcements', 'auto_source_type')) {
                $table->string('auto_source_type')->nullable()->after('is_auto_generated');
            }
            if (! Schema::hasColumn('announcements', 'auto_source_id')) {
                $table->unsignedBigInteger('auto_source_id')->nullable()->after('auto_source_type');
            }
            if (! Schema::hasColumn('announcements', 'view_count')) {
                $table->unsignedInteger('view_count')->default(0)->after('auto_source_id');
            }
            if (! Schema::hasColumn('announcements', 'notifications_sent_count')) {
                $table->unsignedInteger('notifications_sent_count')->default(0)->after('view_count');
            }
            if (! Schema::hasColumn('announcements', 'last_viewed_at')) {
                $table->timestamp('last_viewed_at')->nullable()->after('notifications_sent_count');
            }
            if (! Schema::hasColumn('announcements', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('portal_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_notifications', 'announcement_id')) {
                $table->foreignId('announcement_id')->nullable()->after('created_by')->constrained('announcements')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('portal_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('portal_notifications', 'announcement_id')) {
                $table->dropConstrainedForeignId('announcement_id');
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            $columns = [
                'category', 'priority', 'target_audiences', 'target_grade_level', 'target_section',
                'related_module', 'related_id', 'banner_path', 'banner_variants', 'expires_at',
                'status', 'is_pinned', 'notify_in_app', 'show_on_dashboard', 'pin_to_homepage',
                'send_email', 'is_auto_generated', 'auto_source_type', 'auto_source_id',
                'view_count', 'notifications_sent_count', 'last_viewed_at', 'updated_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('announcements', $column)) {
                    if ($column === 'updated_by') {
                        $table->dropConstrainedForeignId('updated_by');
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
