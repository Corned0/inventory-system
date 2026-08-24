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
        Schema::create('item_boms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->string('name', 150);

            $table->unsignedInteger('version');

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->unique(
                ['item_id', 'version'],
                'item_boms_item_version_unique'
            );

            $table->index(
                ['item_id', 'is_active'],
                'item_boms_item_active_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_boms');
    }
};
