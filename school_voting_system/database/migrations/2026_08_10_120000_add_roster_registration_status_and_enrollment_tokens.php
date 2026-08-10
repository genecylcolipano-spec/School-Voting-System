<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['allowed_students', 'allowed_faculty', 'allowed_administrators'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'registration_status')) {
                    $blueprint->string('registration_status', 40)
                        ->default('not_registered')
                        ->after('is_registered');
                    $blueprint->index('registration_status');
                }

                if (! Schema::hasColumn($table, 'enrollment_pending_at')) {
                    $blueprint->timestamp('enrollment_pending_at')->nullable()->after('registration_status');
                }

                if (! Schema::hasColumn($table, 'registered_at')) {
                    $blueprint->timestamp('registered_at')->nullable()->after('enrollment_pending_at');
                }
            });

            DB::table($table)->where('is_registered', true)->update([
                'registration_status' => 'registered',
                'registered_at' => DB::raw('COALESCE(registered_at, updated_at, created_at)'),
            ]);

            DB::table($table)->where('is_registered', false)->whereNull('registration_status')->update([
                'registration_status' => 'not_registered',
            ]);
        }

        if (! Schema::hasTable('enrollment_tokens')) {
            Schema::create('enrollment_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('token_hash', 64)->unique();
                $table->string('roster_type', 30);
                $table->unsignedBigInteger('roster_id');
                $table->string('account_id', 50);
                $table->string('email');
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('role', 30);
                $table->json('payload')->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamp('invalidated_at')->nullable();
                $table->timestamps();

                $table->index(['roster_type', 'roster_id']);
                $table->index(['account_id', 'expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_tokens');

        foreach (['allowed_students', 'allowed_faculty', 'allowed_administrators'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'registered_at')) {
                    $blueprint->dropColumn('registered_at');
                }
                if (Schema::hasColumn($table, 'enrollment_pending_at')) {
                    $blueprint->dropColumn('enrollment_pending_at');
                }
                if (Schema::hasColumn($table, 'registration_status')) {
                    $blueprint->dropIndex(['registration_status']);
                    $blueprint->dropColumn('registration_status');
                }
            });
        }
    }
};
