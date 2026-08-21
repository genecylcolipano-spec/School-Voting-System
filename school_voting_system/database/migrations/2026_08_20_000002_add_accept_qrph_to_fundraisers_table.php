<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fundraisers', function (Blueprint $table) {
            if (! Schema::hasColumn('fundraisers', 'accept_qrph')) {
                $table->boolean('accept_qrph')->default(true)->after('accept_maya');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fundraisers', function (Blueprint $table) {
            if (Schema::hasColumn('fundraisers', 'accept_qrph')) {
                $table->dropColumn('accept_qrph');
            }
        });
    }
};
