<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('election_partylist')) {
            Schema::create('election_partylist', function (Blueprint $table) {
                $table->id();
                $table->foreignId('election_id')->constrained()->cascadeOnDelete();
                $table->foreignId('partylist_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['election_id', 'partylist_id']);
            });
        }

        // Backfill the pivot from the existing one-to-one election_id column so
        // historical campaign/election links are preserved.
        if (Schema::hasColumn('partylists', 'election_id')) {
            $now = now();

            DB::table('partylists')
                ->whereNotNull('election_id')
                ->orderBy('id')
                ->chunkById(200, function ($partylists) use ($now) {
                    $rows = [];

                    foreach ($partylists as $partylist) {
                        $exists = DB::table('election_partylist')
                            ->where('election_id', $partylist->election_id)
                            ->where('partylist_id', $partylist->id)
                            ->exists();

                        if (! $exists) {
                            $rows[] = [
                                'election_id' => $partylist->election_id,
                                'partylist_id' => $partylist->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    if ($rows !== []) {
                        DB::table('election_partylist')->insert($rows);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('election_partylist');
    }
};
