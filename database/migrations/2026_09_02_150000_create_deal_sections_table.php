<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "Deals this week" band on the storefront homepage: its heading, the
     * decorative image behind it, and the four promises along the bottom. The
     * cards themselves live in deal_cards.
     */
    public function up(): void
    {
        Schema::create('deal_sections', function (Blueprint $table) {
            $table->id();

            // Words wrapped in *asterisks* render in the brand green.
            $table->text('heading');
            $table->string('subheading')->nullable();

            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();

            // [{ "icon": "banknote", "title": "Best prices", "subtitle": "..." }]
            $table->json('trust_items')->nullable();

            $table->boolean('status')->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_sections');
    }
};
