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
        Schema::create('purchase_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_inventory_id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->integer('qty');
            $table->decimal('rate');
            $table->timestamps();
            $table->foreign('purchase_inventory_id')->references('id')->on('purchase_inventory')->onDelete('cascade');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_inventory_items');
    }
};
