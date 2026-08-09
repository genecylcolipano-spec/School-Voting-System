<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partylists', function (Blueprint $table) {
            if (! Schema::hasColumn('partylists', 'color')) {
                $table->string('color', 20)->nullable()->after('motto');
            }
            if (! Schema::hasColumn('partylists', 'description')) {
                $table->text('description')->nullable()->after('platform');
            }
            if (! Schema::hasColumn('partylists', 'leader')) {
                $table->string('leader')->nullable()->after('description');
            }
            if (! Schema::hasColumn('partylists', 'banner_path')) {
                $table->string('banner_path')->nullable()->after('logo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partylists', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['color', 'description', 'leader', 'banner_path'],
                fn (string $column) => Schema::hasColumn('partylists', $column),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
