<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who is waiting for something to come back into stock - the "Notify me"
 * button on an out-of-stock product.
 *
 * A row is a standing request and nothing more: when the units land, the
 * customer is told and the row goes. That also makes the live count of rows
 * the honest answer to "how many people want this", which is what the shop
 * sees on its own low-stock notification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Asking twice is the same request, not two of them.
            $table->unique(['inventory_item_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
