<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The latch that stops the shop's low-stock bell repeating itself.
 *
 * Net stock is not stored anywhere - it is purchases minus sales, worked out
 * on read - so there is no "before" value to compare against when it moves.
 * This column remembers what the shop was last told about this item: 'low',
 * 'out', or nothing. An item is only announced when it crosses into a level
 * it was not already at, and restocking clears it so the next fall announces
 * again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('stock_alert_level', 16)->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('stock_alert_level');
        });
    }
};
