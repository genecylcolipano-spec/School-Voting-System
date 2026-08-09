<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        // SQLite stores role as a plain string; MySQL uses ENUM and must be altered.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'admin', 'super_admin', 'faculty') NOT NULL DEFAULT 'student'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        DB::table('users')
            ->where('role', 'faculty')
            ->update(['role' => 'student']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'admin', 'super_admin') NOT NULL DEFAULT 'student'");
        }
    }
};
