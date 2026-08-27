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
        Schema::create('sales_payment_mode', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_id');
            $table->unsignedBigInteger('payment_mode_id');
            $table->integer('amount');
            $table->timestamps();
            $table->foreign('sales_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('payment_mode_id')->references('id')->on('payment_modes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_payment_mode');
    }
};
