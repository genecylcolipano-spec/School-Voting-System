<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('starts_at')->nullable()->after('event_date');
            $table->dateTime('ends_at')->nullable()->after('starts_at');
        });

        $rows = DB::table('events')->orderBy('id')->get();

        foreach ($rows as $row) {
            if (! $row->event_date) {
                continue;
            }

            $start = Carbon::parse($row->event_date);

            DB::table('events')->where('id', $row->id)->update([
                'starts_at' => $start,
                'ends_at' => $start->copy()->endOfDay(),
            ]);
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['event_date', 'status']);
            $table->dropColumn('event_date');
            $table->index(['starts_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('event_date')->nullable()->after('description');
        });

        $rows = DB::table('events')->orderBy('id')->get();

        foreach ($rows as $row) {
            if (! $row->starts_at) {
                continue;
            }

            DB::table('events')->where('id', $row->id)->update([
                'event_date' => $row->starts_at,
            ]);
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['starts_at', 'status']);
            $table->dropColumn(['starts_at', 'ends_at']);
            $table->index(['event_date', 'status']);
        });
    }
};
