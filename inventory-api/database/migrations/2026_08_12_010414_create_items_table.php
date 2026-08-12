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
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->string('item_code', 50)->unique();
            $table->string('barcode', 100)->nullable()->unique();

            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignId('category_id')
                ->constrained('item_categories');

            $table->foreignId('item_type_id')
                ->constrained('item_types');

            $table->foreignId('unit_of_measure_id')
                ->constrained('units_of_measure');

            $table->decimal('reorder_level', 15, 4)
                ->default(0);

            $table->decimal('reorder_quantity', 15, 4)
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
