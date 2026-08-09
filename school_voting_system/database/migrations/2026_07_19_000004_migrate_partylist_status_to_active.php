<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Adopt the draft/active/inactive/archived vocabulary. The previous
        // "published" value maps to the new "active" state.
        DB::table('partylists')->where('status', 'published')->update(['status' => 'active']);
    }

    public function down(): void
    {
        DB::table('partylists')->where('status', 'active')->update(['status' => 'published']);
        DB::table('partylists')->where('status', 'inactive')->update(['status' => 'archived']);
    }
};
