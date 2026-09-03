<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A storefront order is a sale like any other - same rows, same stock, same
     * reports - it just needs the things a counter sale never had: where it is
     * in fulfilment, that it came from the storefront, and where it is going.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // placed -> confirmed -> shipped -> delivered, or cancelled. A
            // counter sale is done the moment it is rung up, which is why the
            // default is 'delivered' - existing rows take it too.
            $table->string('status', 20)->default('delivered')->after('customer_id');
            // 'counter' for the POS, 'storefront' for an order the customer placed.
            $table->string('channel', 20)->default('counter')->after('status');

            // The address as it was when the order was placed - the customer may
            // edit or delete the address book entry later.
            $table->string('delivery_recipient')->nullable()->after('channel');
            $table->string('delivery_phone', 20)->nullable()->after('delivery_recipient');
            $table->string('delivery_address')->nullable()->after('delivery_phone');
            $table->string('delivery_landmark')->nullable()->after('delivery_address');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('delivery_landmark');

            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['channel', 'status']);
            $table->dropColumn([
                'status',
                'channel',
                'delivery_recipient',
                'delivery_phone',
                'delivery_address',
                'delivery_landmark',
                'delivery_fee',
            ]);
        });
    }
};
