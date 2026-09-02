<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-product feature highlights - the little icon/title/subtitle band on
     * the detail page (pH Balanced, 98% Germ Protection, ...). Product
     * specific, so it lives on the item as JSON rather than a store-wide
     * setting.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // [{ "icon": "droplet", "title": "pH Balanced", "subtitle": "Gentle on hands" }]
            $table->json('highlights')->nullable()->after('country_of_origin');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('highlights');
        });
    }
};
