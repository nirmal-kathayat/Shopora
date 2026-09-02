<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Content the product detail page shows beyond price and stock: a
     * description, and a few spec-table fields. All nullable - a shop that
     * leaves them empty simply shows fewer rows, never a blank.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('compare_at_price');
            $table->string('brand')->nullable()->after('description');
            $table->string('net_volume')->nullable()->after('brand');
            $table->string('country_of_origin')->nullable()->after('net_volume');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'brand', 'net_volume', 'country_of_origin']);
        });
    }
};
