<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One offer card inside a deal section. Cards are edited on the section's
     * own form, so they are ordered by sort_order and deleted with their
     * parent - a card has no meaning on its own.
     */
    public function up(): void
    {
        Schema::create('deal_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_section_id')->constrained()->cascadeOnDelete();

            $table->string('badge_text')->nullable();
            // Words wrapped in *asterisks* render in the accent colour, which
            // is how "Save up to *15%*" gets its highlight.
            $table->text('title');
            $table->string('description')->nullable();

            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();

            // One of DealCard::ICONS - drives both the small chip and, when no
            // image is uploaded, which built-in artwork the card gets.
            $table->string('icon', 30)->default('tag');

            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();

            // The dark card that leads the row.
            $table->boolean('featured')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['deal_section_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_cards');
    }
};
