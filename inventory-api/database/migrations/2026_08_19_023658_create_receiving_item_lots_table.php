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
        Schema::create('receiving_item_lots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('receiving_item_id')
                ->constrained('receiving_items')
                ->cascadeOnDelete();

            $table->foreignId('lot_id')
                ->constrained('inventory_lots')
                ->restrictOnDelete();

            $table->decimal('quantity', 18, 4);

            $table->timestamps();

            $table->unique([
                'receiving_item_id',
                'lot_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiving_item_lots');
    }
};
