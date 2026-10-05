<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('talent_events', 'paid_support_enabled')) {
            Schema::table('talent_events', function (Blueprint $table) {
                $table->boolean('paid_support_enabled')->default(false)->after('published_to_students');
                $table->decimal('vote_price', 12, 2)->default(20)->after('paid_support_enabled');
                $table->decimal('support_amount_raised', 12, 2)->default(0)->after('vote_price');
            });
        }

        if (! Schema::hasTable('talent_vote_orders')) {
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
        }

        if ($this->hasIndex('talent_event_votes', ['user_id', 'talent_event_id'], unique: true)) {
            // MySQL uses this unique index for the user_id foreign key.
            // Drop the FK first, then the unique, then restore a non-unique index + FK.
            if ($this->hasForeignKey('talent_event_votes', ['user_id'])) {
                Schema::table('talent_event_votes', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            }

            Schema::table('talent_event_votes', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'talent_event_id']);
            });

            if (! $this->hasIndex('talent_event_votes', ['user_id', 'talent_event_id'], unique: false)) {
                Schema::table('talent_event_votes', function (Blueprint $table) {
                    $table->index(['user_id', 'talent_event_id']);
                });
            }

            if (! $this->hasForeignKey('talent_event_votes', ['user_id'])) {
                Schema::table('talent_event_votes', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }
        }

        if (! Schema::hasColumn('talent_event_votes', 'talent_vote_order_id')) {
            Schema::table('talent_event_votes', function (Blueprint $table) {
                $table->foreignId('talent_vote_order_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('talent_vote_orders')
                    ->nullOnDelete();
            });
        }

        if (! $this->hasIndex('talent_event_votes', ['talent_event_id', 'talent_event_entry_id'])) {
            Schema::table('talent_event_votes', function (Blueprint $table) {
                $table->index(['talent_event_id', 'talent_event_entry_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('talent_event_votes', 'talent_vote_order_id')) {
            Schema::table('talent_event_votes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('talent_vote_order_id');
            });
        }

        if ($this->hasIndex('talent_event_votes', ['talent_event_id', 'talent_event_entry_id'])) {
            Schema::table('talent_event_votes', function (Blueprint $table) {
                $table->dropIndex(['talent_event_id', 'talent_event_entry_id']);
            });
        }

        if ($this->hasIndex('talent_event_votes', ['user_id', 'talent_event_id'], unique: false)
            && ! $this->hasIndex('talent_event_votes', ['user_id', 'talent_event_id'], unique: true)) {
            if ($this->hasForeignKey('talent_event_votes', ['user_id'])) {
                Schema::table('talent_event_votes', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            }

            Schema::table('talent_event_votes', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'talent_event_id']);
                $table->unique(['user_id', 'talent_event_id']);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        Schema::dropIfExists('talent_vote_orders');

        $eventColumns = collect(['paid_support_enabled', 'vote_price', 'support_amount_raised'])
            ->filter(fn (string $column) => Schema::hasColumn('talent_events', $column))
            ->values()
            ->all();

        if ($eventColumns !== []) {
            Schema::table('talent_events', function (Blueprint $table) use ($eventColumns) {
                $table->dropColumn($eventColumns);
            });
        }
    }

    /**
     * @param  list<string>  $columns
     */
    protected function hasIndex(string $table, array $columns, ?bool $unique = null): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['columns'] ?? []) !== $columns) {
                continue;
            }

            if ($unique === null) {
                return true;
            }

            if ((bool) ($index['unique'] ?? false) === $unique) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $columns
     */
    protected function hasForeignKey(string $table, array $columns): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (($foreignKey['columns'] ?? []) === $columns) {
                return true;
            }
        }

        return false;
    }
};
