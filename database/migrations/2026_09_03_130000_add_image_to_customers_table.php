<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The customer's own photo, uploaded from the storefront account page.
     * Stored as a filename in public/image, like every other upload here.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('image')->nullable()->after('pan_number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
