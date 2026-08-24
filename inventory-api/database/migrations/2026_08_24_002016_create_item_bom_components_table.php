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
        Schema::create('item_bom_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bom_id')
                ->constrained('item_boms')
                ->cascadeOnDelete();

            $table->foreignId('component_item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->decimal('quantity', 18, 4);

            $table->boolean('is_required')
                ->default(true);

            $table->unique(
                ['bom_id', 'component_item_id'],
                'item_bom_components_bom_item_unique'
            );

            $table->index('component_item_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_bom_components');
    }
};
