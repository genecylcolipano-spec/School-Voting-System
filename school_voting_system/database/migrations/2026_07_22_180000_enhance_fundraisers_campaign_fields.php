<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fundraisers', function (Blueprint $table) {
            if (! Schema::hasColumn('fundraisers', 'category')) {
                $table->string('category')->nullable()->after('description');
            }
            if (! Schema::hasColumn('fundraisers', 'beneficiary')) {
                $table->string('beneficiary')->nullable()->after('category');
            }
            if (! Schema::hasColumn('fundraisers', 'purpose')) {
                $table->string('purpose')->nullable()->after('beneficiary');
            }
            if (! Schema::hasColumn('fundraisers', 'expected_beneficiaries')) {
                $table->string('expected_beneficiaries')->nullable()->after('purpose');
            }
            if (! Schema::hasColumn('fundraisers', 'min_donation')) {
                $table->decimal('min_donation', 12, 2)->nullable()->after('goal_amount');
            }
            if (! Schema::hasColumn('fundraisers', 'max_donation')) {
                $table->decimal('max_donation', 12, 2)->nullable()->after('min_donation');
            }
            if (! Schema::hasColumn('fundraisers', 'allow_anonymous')) {
                $table->boolean('allow_anonymous')->default(true)->after('max_donation');
            }
            if (! Schema::hasColumn('fundraisers', 'generate_receipt')) {
                $table->boolean('generate_receipt')->default(true)->after('allow_anonymous');
            }
            if (! Schema::hasColumn('fundraisers', 'accept_cash')) {
                $table->boolean('accept_cash')->default(true)->after('generate_receipt');
            }
            if (! Schema::hasColumn('fundraisers', 'accept_gcash')) {
                $table->boolean('accept_gcash')->default(true)->after('accept_cash');
            }
            if (! Schema::hasColumn('fundraisers', 'accept_maya')) {
                $table->boolean('accept_maya')->default(true)->after('accept_gcash');
            }
            if (! Schema::hasColumn('fundraisers', 'accept_bank_transfer')) {
                $table->boolean('accept_bank_transfer')->default(true)->after('accept_maya');
            }
            if (! Schema::hasColumn('fundraisers', 'banner_path')) {
                $table->string('banner_path')->nullable()->after('accept_bank_transfer');
            }
            if (! Schema::hasColumn('fundraisers', 'banner_variants')) {
                $table->json('banner_variants')->nullable()->after('banner_path');
            }
            if (! Schema::hasColumn('fundraisers', 'visibility')) {
                $table->string('visibility')->default('public')->after('banner_variants');
            }
            if (! Schema::hasColumn('fundraisers', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('visibility');
            }
            if (! Schema::hasColumn('fundraisers', 'accept_donations')) {
                $table->boolean('accept_donations')->default(true)->after('is_featured');
            }
            if (! Schema::hasColumn('fundraisers', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        // Expand status column beyond the original MySQL enum values.
        Schema::table('fundraisers', function (Blueprint $table) {
            $table->string('status', 32)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('fundraisers', function (Blueprint $table) {
            $columns = [
                'category',
                'beneficiary',
                'purpose',
                'expected_beneficiaries',
                'min_donation',
                'max_donation',
                'allow_anonymous',
                'generate_receipt',
                'accept_cash',
                'accept_gcash',
                'accept_maya',
                'accept_bank_transfer',
                'banner_path',
                'banner_variants',
                'visibility',
                'is_featured',
                'accept_donations',
                'updated_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('fundraisers', $column)) {
                    if ($column === 'updated_by') {
                        $table->dropConstrainedForeignId('updated_by');
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
