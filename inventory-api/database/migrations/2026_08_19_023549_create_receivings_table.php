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
        Schema::create('receivings', function (Blueprint $table) {
            $table->id();

            $table->string('receiving_number', 50)->unique();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable();
                /* ->constrained('purchase_orders')
                ->restrictOnDelete(); */

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->dateTime('received_date');

            $table->string('status', 30)
                ->default('draft');

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('supplier_id');
            $table->index('purchase_order_id');
            $table->index('warehouse_id');
            $table->index('status');
            $table->index('received_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivings');
    }
};
