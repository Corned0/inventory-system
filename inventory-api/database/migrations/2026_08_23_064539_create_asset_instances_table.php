<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AssetInstanceStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_instances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->string('asset_number', 50)
                ->unique();

            $table->string('serial_number', 100)
                ->unique();

            $table->string('status', 30)
                ->default(AssetInstanceStatus::Available->value);

            $table->foreignId('current_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('current_location_id')
                ->nullable()
                ->constrained('locations')
                ->restrictOnDelete();

            $table->timestamp('acquired_at')
                ->nullable();

            $table->decimal('acquisition_cost', 18, 4)
                ->nullable();

            $table->date('warranty_start')
                ->nullable();

            $table->date('warranty_end')
                ->nullable();

            $table->timestamps();

            $table->index('item_id');
            $table->index('status');
            $table->index('current_warehouse_id');
            $table->index('current_location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_instances');
    }
};