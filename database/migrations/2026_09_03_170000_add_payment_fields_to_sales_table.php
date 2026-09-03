<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How an order was paid for. A counter sale and a cash-on-delivery order
     * carry the defaults; an online payment (eSewa for now) fills the rest.
     * The gateway's own reference is kept so a payment can be traced or, later,
     * reconciled against the provider.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // 'cod' for cash on delivery / the counter, 'esewa' for a paid one.
            $table->string('payment_method', 20)->default('cod')->after('delivery_fee');
            // 'unpaid' until the money is in - COD stays unpaid until delivery,
            // an online order flips to 'paid' when the gateway confirms it.
            $table->string('payment_status', 20)->default('unpaid')->after('payment_method');
            // The gateway's transaction reference, once it has one.
            $table->string('payment_ref')->nullable()->after('payment_status');
            // The id we hand the gateway to identify this attempt; we look the
            // order back up by it when the customer returns from paying.
            $table->string('payment_uuid', 60)->nullable()->unique()->after('payment_ref');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['payment_uuid']);
            $table->dropColumn(['payment_method', 'payment_status', 'payment_ref', 'payment_uuid']);
        });
    }
};
