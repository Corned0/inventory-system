<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->restrictOnDelete();

            $table->foreignId('lot_id')
                ->nullable()
                ->constrained('inventory_lots')
                ->restrictOnDelete();

            $table->decimal('quantity', 18, 4)
                ->default(0);

            $table->decimal('reserved_quantity', 18, 4)
                ->default(0);

            $table->decimal('available_quantity', 18, 4)
                ->default(0);

            $table->timestamps();

            $table->index(['item_id', 'warehouse_id']);
            $table->index('location_id');
            $table->index('lot_id');
        });

        DB::statement(
            'CREATE UNIQUE INDEX inventory_balances_unique_key
             ON inventory_balances (
                 item_id,
                 warehouse_id,
                 location_id,
                 lot_id
             )
             NULLS NOT DISTINCT'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};