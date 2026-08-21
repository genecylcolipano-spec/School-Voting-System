<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('is_anonymous');
            $table->string('payment_method', 30)->nullable()->after('status');
            $table->string('paymongo_checkout_session_id')->nullable()->after('payment_method');
            $table->string('paymongo_payment_id')->nullable()->after('paymongo_checkout_session_id');
            $table->timestamp('paid_at')->nullable()->after('donated_at');

            $table->index(['status', 'fundraiser_id']);
            $table->unique('paymongo_checkout_session_id');
        });

        // Existing rows were counted immediately; keep them as paid without touching amount_raised.
        DB::table('donations')
            ->whereNull('paid_at')
            ->update([
                'status' => 'paid',
                'paid_at' => DB::raw('donated_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropUnique(['paymongo_checkout_session_id']);
            $table->dropIndex(['status', 'fundraiser_id']);
            $table->dropColumn([
                'status',
                'payment_method',
                'paymongo_checkout_session_id',
                'paymongo_payment_id',
                'paid_at',
            ]);
        });
    }
};
