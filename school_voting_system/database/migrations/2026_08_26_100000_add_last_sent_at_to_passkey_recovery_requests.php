<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passkey_recovery_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('passkey_recovery_requests', 'last_sent_at')) {
                $table->timestamp('last_sent_at')->nullable()->after('resolved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('passkey_recovery_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('passkey_recovery_requests', 'last_sent_at')) {
                $table->dropColumn('last_sent_at');
            }
        });
    }
};
