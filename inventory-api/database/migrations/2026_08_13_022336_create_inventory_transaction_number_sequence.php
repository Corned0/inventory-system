<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            'CREATE SEQUENCE IF NOT EXISTS inventory_transaction_number_sequence
             START WITH 1
             INCREMENT BY 1'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            'DROP SEQUENCE IF EXISTS inventory_transaction_number_sequence'
        );
    }
};
