<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passkey_recovery_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('passkey_recovery_requests', 'token_hash')) {
                $table->string('token_hash', 64)->nullable()->after('email');
            }
            if (! Schema::hasColumn('passkey_recovery_requests', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('token_hash');
            }
            if (! Schema::hasColumn('passkey_recovery_requests', 'used_at')) {
                $table->timestamp('used_at')->nullable()->after('expires_at');
            }
            if (! Schema::hasColumn('passkey_recovery_requests', 'invalidated_at')) {
                $table->timestamp('invalidated_at')->nullable()->after('used_at');
            }
        });

        Schema::table('passkey_recovery_requests', function (Blueprint $table): void {
            $table->index('token_hash');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('passkey_recovery_requests', function (Blueprint $table): void {
            $table->dropIndex(['token_hash']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn(['token_hash', 'expires_at', 'used_at', 'invalidated_at']);
        });
    }
};
