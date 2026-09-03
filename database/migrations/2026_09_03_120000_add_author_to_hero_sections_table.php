<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The byline under the pitch — who is behind the shop. Both are optional;
     * the storefront only draws the line when there is a name.
     */
    public function up(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('subheading');
            $table->string('author_image')->nullable()->after('author_name');
        });
    }

    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'author_image']);
        });
    }
};
