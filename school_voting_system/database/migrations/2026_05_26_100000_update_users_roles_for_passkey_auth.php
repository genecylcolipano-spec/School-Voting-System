<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->where('role', 'teacher')
                ->update(['role' => 'student']);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'admin', 'super admin') NOT NULL DEFAULT 'student'");
            DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'teacher', 'admin') NOT NULL DEFAULT 'student'");
        }
    }
};
