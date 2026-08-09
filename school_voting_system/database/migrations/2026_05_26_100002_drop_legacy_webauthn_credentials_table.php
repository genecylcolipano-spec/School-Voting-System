<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('webauthn_credentials');
    }

    public function down(): void
    {
        // Legacy Laragear table is not recreated on rollback.
    }
};
