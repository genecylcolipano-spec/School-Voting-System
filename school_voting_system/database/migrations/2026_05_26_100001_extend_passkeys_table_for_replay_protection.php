<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extends the laravel/passkeys table with explicit replay-protection columns.
     */
    public function up(): void
    {
        Schema::table('passkeys', function (Blueprint $table) {
            $table->unsignedBigInteger('counter')->default(0)->after('credential');
            $table->longText('public_key')->nullable()->after('counter');
            $table->string('device_name')->nullable()->after('public_key');
        });

        Schema::table('passkeys', function (Blueprint $table) {
            $table->index(['user_id', 'counter']);
        });
    }

    public function down(): void
    {
        Schema::table('passkeys', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'counter']);
            $table->dropColumn(['counter', 'public_key', 'device_name']);
        });
    }
};
