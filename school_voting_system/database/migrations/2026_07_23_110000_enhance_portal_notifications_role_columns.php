<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_notifications', function (Blueprint $table) {
            $table->string('module', 40)->nullable()->after('type');
            $table->string('recipient_role', 30)->nullable()->after('user_id');
            $table->unsignedBigInteger('related_id')->nullable()->after('announcement_id');
            $table->index(['module', 'created_at']);
            $table->index(['recipient_role', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::table('portal_notifications', function (Blueprint $table) {
            $table->dropIndex(['module', 'created_at']);
            $table->dropIndex(['recipient_role', 'read_at']);
            $table->dropColumn(['module', 'recipient_role', 'related_id']);
        });
    }
};
