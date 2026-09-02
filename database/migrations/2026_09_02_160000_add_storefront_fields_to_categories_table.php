<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Categories were an id and a title, enough to group inventory. The
     * storefront also shows them as a browsable aisle, which needs a URL slug,
     * an icon, a shelf photo, an order and an on/off switch.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->string('icon', 30)->default('grid')->after('slug');
            $table->string('image')->nullable()->after('icon');
            $table->string('image_alt')->nullable()->after('image');
            $table->boolean('status')->default(1)->after('image_alt');
            $table->unsignedInteger('sort_order')->default(0)->after('status');
            $table->integer('created_by')->nullable()->after('sort_order');
            $table->integer('updated_by')->nullable()->after('created_by');
        });

        // Backfill the six seeded categories: a slug from the title, an icon
        // matched to the name, and the order they already appear in.
        $icons = [
            'Electronics' => 'monitor',
            'Grocery' => 'basket',
            'Stationery' => 'pencil',
            'Hardware' => 'hammer',
            'Beverages' => 'cup',
            'Personal Care' => 'sparkles',
        ];

        foreach (DB::table('categories')->orderBy('id')->get() as $index => $category) {
            DB::table('categories')->where('id', $category->id)->update([
                'slug' => Str::slug($category->title),
                'icon' => $icons[$category->title] ?? 'grid',
                'sort_order' => $index,
            ]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('slug');
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_slug_unique');
            $table->dropIndex(['status', 'sort_order']);
            $table->dropColumn([
                'slug', 'icon', 'image', 'image_alt',
                'status', 'sort_order', 'created_by', 'updated_by',
            ]);
        });
    }
};
