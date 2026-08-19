<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "CREATE SEQUENCE IF NOT EXISTS receiving_number_sequence
            START 1
            INCREMENT 1"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "DROP SEQUENCE IF EXISTS receiving_number_sequence"
        );
    }
};
