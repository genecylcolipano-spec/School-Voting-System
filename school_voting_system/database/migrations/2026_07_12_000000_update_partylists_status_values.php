<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('partylists')->where('status', 'active')->update(['status' => 'published']);
        DB::table('partylists')->where('status', 'inactive')->update(['status' => 'archived']);

        Schema::table('partylists', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->change();
        });
    }

    public function down(): void
    {
        DB::table('partylists')->where('status', 'published')->update(['status' => 'active']);
        DB::table('partylists')->where('status', 'archived')->update(['status' => 'inactive']);
        DB::table('partylists')->where('status', 'draft')->update(['status' => 'active']);

        Schema::table('partylists', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->change();
        });
    }
};
