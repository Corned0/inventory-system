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
        Schema::create('receiving_item_serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('receiving_item_id')
                ->constrained('receiving_items')
                ->cascadeOnDelete();

            $table->foreignId('serial_id')
                ->constrained('inventory_serials')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique([
                'receiving_item_id',
                'serial_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiving_item_serials');
    }
};
