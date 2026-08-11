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
        Schema::create('item_types', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('tracking_type', 20)
                ->default('none');

            $table->boolean('is_asset')->default(false);
            $table->boolean('is_composite')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('tracking_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_types');
    }
};
