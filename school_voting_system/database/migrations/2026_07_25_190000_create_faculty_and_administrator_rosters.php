<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('allowed_students') && ! Schema::hasColumn('allowed_students', 'archived_at')) {
            Schema::table('allowed_students', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('is_registered');
            });
        }

        if (! Schema::hasTable('allowed_faculty')) {
            Schema::create('allowed_faculty', function (Blueprint $table) {
                $table->id();
                $table->string('account_id', 50)->unique();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('department', 120)->nullable();
                $table->string('position', 120)->nullable();
                $table->boolean('is_registered')->default(false);
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('allowed_administrators')) {
            Schema::create('allowed_administrators', function (Blueprint $table) {
                $table->id();
                $table->string('account_id', 50)->unique();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('department', 120)->nullable();
                $table->string('position', 120)->nullable();
                $table->boolean('is_registered')->default(false);
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('allowed_administrators');
        Schema::dropIfExists('allowed_faculty');

        if (Schema::hasTable('allowed_students') && Schema::hasColumn('allowed_students', 'archived_at')) {
            Schema::table('allowed_students', function (Blueprint $table) {
                $table->dropColumn('archived_at');
            });
        }
    }
};
