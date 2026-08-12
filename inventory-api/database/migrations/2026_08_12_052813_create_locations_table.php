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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('locations')
                ->restrictOnDelete();

            $table->string('code', 50);
            $table->string('name', 255);

            $table->string('location_type', 20);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->unique([
                'warehouse_id',
                'code',
            ]);

            $table->index([
                'warehouse_id',
                'parent_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
