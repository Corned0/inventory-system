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
        Schema::create('receiving_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('receiving_id')
                ->constrained('receivings')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->decimal('ordered_quantity', 18, 4)
                ->default(0);

            $table->decimal('received_quantity', 18, 4)
                ->default(0);

            $table->decimal('accepted_quantity', 18, 4)
                ->default(0);

            $table->decimal('rejected_quantity', 18, 4)
                ->default(0);

            $table->decimal('unit_cost', 18, 4)
                ->nullable();

            $table->timestamps();

            $table->index('receiving_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiving_items');
    }
};
