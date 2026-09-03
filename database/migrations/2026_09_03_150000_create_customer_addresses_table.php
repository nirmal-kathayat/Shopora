<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A customer's delivery addresses. The single customers.address column
     * stays as the shipping line the rest of the shop already reads - it is
     * kept in step with whichever address is the default here.
     */
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // "Home", "Office", or whatever the customer calls it.
            $table->string('label', 40)->nullable();
            $table->string('recipient_name');
            $table->string('ph_number', 20);

            $table->string('city');
            $table->string('area')->nullable();
            $table->string('street');
            $table->string('landmark')->nullable();

            // Exactly one per customer, enforced when writing.
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['customer_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
