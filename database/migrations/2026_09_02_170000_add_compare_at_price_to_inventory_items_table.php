<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A "was" price, so the storefront can show a strike-through and a discount
     * badge. Only meaningful when it is above price_per_unit; the storefront
     * ignores it otherwise, so a stale or lower value never shows a fake deal.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('compare_at_price', 10, 2)->nullable()->after('price_per_unit');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('compare_at_price');
        });
    }
};
