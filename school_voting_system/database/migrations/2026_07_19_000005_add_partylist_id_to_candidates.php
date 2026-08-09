<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('candidates', 'partylist_id')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->foreignId('partylist_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        $this->backfillPartylistIds();
    }

    /**
     * Best-effort backfill: match a candidate's free-text party_or_group to a
     * campaign (by normalized name or acronym) attached to the same election.
     */
    protected function backfillPartylistIds(): void
    {
        $partylists = DB::table('partylists')->get(['id', 'name', 'acronym']);

        if ($partylists->isEmpty()) {
            return;
        }

        $normalize = static fn (?string $value): string => strtolower(trim((string) $value));

        DB::table('candidates')
            ->whereNull('partylist_id')
            ->whereNotNull('party_or_group')
            ->orderBy('id')
            ->chunkById(200, function ($candidates) use ($partylists, $normalize) {
                foreach ($candidates as $candidate) {
                    $key = $normalize($candidate->party_or_group);

                    if ($key === '') {
                        continue;
                    }

                    $electionPartylistIds = DB::table('election_partylist')
                        ->where('election_id', $candidate->election_id)
                        ->pluck('partylist_id')
                        ->all();

                    $match = $partylists->first(function ($partylist) use ($key, $normalize, $electionPartylistIds) {
                        if (! in_array($partylist->id, $electionPartylistIds, true)) {
                            return false;
                        }

                        return $normalize($partylist->name) === $key
                            || $normalize($partylist->acronym) === $key;
                    });

                    if ($match) {
                        DB::table('candidates')
                            ->where('id', $candidate->id)
                            ->update(['partylist_id' => $match->id]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('candidates', 'partylist_id')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->dropConstrainedForeignId('partylist_id');
            });
        }
    }
};
