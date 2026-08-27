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
        Schema::create('purchase_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('vendor');
            $table->date('bill_date');
            $table->string('address')->nullable();
            $table->string('pan_number')->nullable();
            $table->decimal('vat_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_inventory');
    }
};
