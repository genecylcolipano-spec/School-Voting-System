<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'student_id') && ! Schema::hasColumn('users', 'account_id')) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                Schema::table('users', function (Blueprint $table) {
                    $table->renameColumn('student_id', 'account_id');
                });
            } else {
                DB::statement('ALTER TABLE users CHANGE student_id account_id VARCHAR(50) NULL');
            }
        }

        if (Schema::hasColumn('passkey_recovery_requests', 'student_id')
            && ! Schema::hasColumn('passkey_recovery_requests', 'account_id')) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                Schema::table('passkey_recovery_requests', function (Blueprint $table) {
                    $table->renameColumn('student_id', 'account_id');
                });
            } else {
                DB::statement('ALTER TABLE passkey_recovery_requests CHANGE student_id account_id VARCHAR(50) NOT NULL');
            }
        }

        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->where('role', 'super admin')
                ->update(['role' => 'super_admin']);

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'admin', 'super_admin') NOT NULL DEFAULT 'student'");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->where('role', 'super_admin')
                ->update(['role' => 'super admin']);

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'admin', 'super admin') NOT NULL DEFAULT 'student'");
            }
        }

        if (Schema::hasColumn('passkey_recovery_requests', 'account_id')
            && ! Schema::hasColumn('passkey_recovery_requests', 'student_id')) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                Schema::table('passkey_recovery_requests', function (Blueprint $table) {
                    $table->renameColumn('account_id', 'student_id');
                });
            } else {
                DB::statement('ALTER TABLE passkey_recovery_requests CHANGE account_id student_id VARCHAR(50) NOT NULL');
            }
        }

        if (Schema::hasColumn('users', 'account_id') && ! Schema::hasColumn('users', 'student_id')) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                Schema::table('users', function (Blueprint $table) {
                    $table->renameColumn('account_id', 'student_id');
                });
            } else {
                DB::statement('ALTER TABLE users CHANGE account_id student_id VARCHAR(50) NULL');
            }
        }
    }
};
