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
        Schema::create('item_type_attributes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('attribute_definition_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique([
                'item_type_id',
                'attribute_definition_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_type_attributes');
    }
};
