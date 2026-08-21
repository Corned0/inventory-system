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
        Schema::create('put_away_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('put_away_id')
                ->constrained('put_aways')
                ->cascadeOnDelete();

            $table->foreignId('receiving_item_id')
                ->constrained('receiving_items')
                ->restrictOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->foreignId('location_id')
                ->constrained('locations')
                ->restrictOnDelete();

            $table->foreignId('lot_id')
                ->nullable()
                ->constrained('inventory_lots')
                ->restrictOnDelete();

            $table->foreignId('serial_id')
                ->nullable()
                ->constrained('inventory_serials')
                ->restrictOnDelete();

            $table->decimal('quantity', 18, 4);

            $table->timestamps();

            $table->index('put_away_id');
            $table->index('receiving_item_id');
            $table->index('item_id');
            $table->index('location_id');
            $table->index('lot_id');
            $table->index('serial_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('put_away_items');
    }
};
