<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_events', function (Blueprint $table) {
            $table->boolean('paid_support_enabled')->default(false)->after('published_to_students');
            $table->decimal('vote_price', 12, 2)->default(20)->after('paid_support_enabled');
            $table->decimal('support_amount_raised', 12, 2)->default(0)->after('vote_price');
        });

        Schema::create('talent_vote_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talent_event_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PHP');
            $table->string('status', 20)->default('pending');
            $table->string('payment_method', 30)->default('qrph');
            $table->string('paymongo_checkout_session_id')->nullable();
            $table->string('paymongo_payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('votes_credited_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['talent_event_id', 'status']);
            $table->index(['user_id', 'talent_event_id']);
            $table->index('paymongo_checkout_session_id');
        });

        Schema::table('talent_event_votes', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'talent_event_id']);
            $table->foreignId('talent_vote_order_id')
                ->nullable()
                ->after('user_id')
                ->constrained('talent_vote_orders')
                ->nullOnDelete();
            $table->index(['user_id', 'talent_event_id']);
            $table->index(['talent_event_id', 'talent_event_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::table('talent_event_votes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('talent_vote_order_id');
            $table->dropIndex(['user_id', 'talent_event_id']);
            $table->dropIndex(['talent_event_id', 'talent_event_entry_id']);
            $table->unique(['user_id', 'talent_event_id']);
        });

        Schema::dropIfExists('talent_vote_orders');

        Schema::table('talent_events', function (Blueprint $table) {
            $table->dropColumn(['paid_support_enabled', 'vote_price', 'support_amount_raised']);
        });
    }
};
