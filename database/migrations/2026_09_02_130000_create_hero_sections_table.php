<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Content for the storefront hero. Several rows may exist, but only the
     * one with status = 1 is served, so the shop can prepare a campaign and
     * switch to it in one click.
     */
    public function up(): void
    {
        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();

            $table->string('badge_text')->nullable();
            // Words wrapped in *asterisks* render in the brand green, and a
            // line break in the text becomes one on wide screens.
            $table->text('heading');
            $table->text('subheading')->nullable();

            $table->string('primary_label')->nullable();
            $table->string('primary_url')->nullable();
            $table->string('secondary_label')->nullable();
            $table->string('secondary_url')->nullable();

            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();

            // [{ "label": "Rice", "url": "/products?q=Rice" }, ...]
            $table->json('popular_searches')->nullable();

            $table->string('delivery_title')->nullable();
            $table->string('delivery_subtitle')->nullable();
            $table->string('trust_label')->nullable();
            $table->string('trust_value')->nullable();
            $table->string('trust_subtitle')->nullable();

            $table->boolean('status')->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_sections');
    }
};
