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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_number', 50)->unique();

            $table->string('transaction_type', 30);

            $table->foreignId('item_id')
                ->constrained('items');

            $table->foreignId('warehouse_id')
                ->constrained('warehouses');

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations');

            $table->foreignId('lot_id')
                ->nullable()
                ->constrained('inventory_lots');

            $table->foreignId('serial_id')
                ->nullable()
                ->constrained('inventory_serials');

            $table->decimal('quantity', 15, 4);

            $table->decimal('unit_cost', 15, 4)
                ->nullable();

            $table->string('reference_type', 100)
                ->nullable();

            $table->unsignedBigInteger('reference_id')
                ->nullable();

            $table->timestamp('transaction_date');

            $table->foreignId('performed_by')
                ->constrained('users');

            $table->text('remarks')
                ->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->index([
                'item_id',
                'warehouse_id',
                'location_id',
            ]);

            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index([
                'reference_type',
                'reference_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
