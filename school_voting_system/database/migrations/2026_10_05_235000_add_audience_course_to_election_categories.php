<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_categories', function (Blueprint $table) {
            $table->string('audience_course', 20)->nullable()->after('max_selections');
            $table->index(['election_id', 'audience_course']);
        });
    }

    public function down(): void
    {
        Schema::table('election_categories', function (Blueprint $table) {
            $table->dropIndex(['election_id', 'audience_course']);
            $table->dropColumn('audience_course');
        });
    }
};
