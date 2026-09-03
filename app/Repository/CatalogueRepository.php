<?php

namespace App\Repository;

use App\Models\InventoryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The storefront-facing view of the catalogue: inventory items dressed with
 * their live stock, their category and how well they sell. Read-only - the
 * admin side edits items through InventoryItemRepository. Separate from the
 * legacy ProductRepository, which serves a different, older products table.
 */
class CatalogueRepository
{
    /** At or below this many units in stock reads as "low" rather than "in". */
    public const LOW_STOCK_THRESHOLD = 5;

    private InventoryItem $query;

    public function __construct(InventoryItem $query)
    {
        $this->query = $query;
    }

    /**
     * A filtered, sorted, paginated page of products.
     *
     * @param array{
     *   category?: string|null, q?: string|null, sort?: string|null,
     *   availability?: array<int,string>, min_price?: float|null, max_price?: float|null,
     *   per_page?: int, page?: int
     * } $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        $this->applyCategory($query, $filters['category'] ?? null);
        $this->applySearch($query, $filters['q'] ?? null);
        $this->applyPrice($query, $filters['min_price'] ?? null, $filters['max_price'] ?? null);
        $this->applyAvailability($query, $filters['availability'] ?? []);
        $this->applySort($query, $filters['sort'] ?? 'featured');

        $perPage = min(max((int) ($filters['per_page'] ?? 24), 1), 48);

        return $query->paginate($perPage)->withQueryString();
    }

    /** One product for the detail page, with the extra detail columns. */
    public function find(int $id): ?object
    {
        return $this->baseQuery()
            ->addSelect([
                'inventory_items.description',
                'inventory_items.brand',
                'inventory_items.net_volume',
                'inventory_items.country_of_origin',
                'inventory_items.highlights',
                'inventory_items.features',
            ])
            ->where('inventory_items.id', $id)
            ->with('productImages')
            ->first();
    }

    /** Several products by id, with stock and category, for the cart. */
    public function findMany(array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        return $this->baseQuery()
            ->whereIn('inventory_items.id', $ids)
            ->get();
    }

    /** The item ids that exist, so an unknown category slug can 404 cleanly. */
    public function categoryExists(string $slug): bool
    {
        return DB::table('categories')->where('slug', $slug)->exists();
    }

    /**
     * inventory_items with the columns the storefront needs, plus three
     * computed values every query wants: net stock, category slug/title, and
     * units sold (for best-seller ranking). Kept as subquery selects so the
     * grouping stays simple and each item is one row.
     */
    private function baseQuery(): Builder
    {
        $netStock = DB::table('inventory_stocks')
            ->selectRaw('COALESCE(SUM(CASE WHEN purchase_inventory_id IS NOT NULL THEN qty ELSE 0 END)'
                . ' - SUM(CASE WHEN sales_id IS NOT NULL THEN qty ELSE 0 END), 0)')
            ->whereColumn('inventory_stocks.inventory_item_id', 'inventory_items.id');

        $unitsSold = DB::table('sales_products')
            ->join('sales', 'sales.id', '=', 'sales_products.sales_id')
            ->selectRaw('COALESCE(SUM(sales_products.qty), 0)')
            ->whereNotIn('sales.status', ['cancelled', 'pending_payment'])
            ->whereColumn('sales_products.product_id', 'inventory_items.id');

        $reviewAvg = DB::table('product_reviews')
            ->selectRaw('AVG(rating)')
            ->whereColumn('product_reviews.inventory_item_id', 'inventory_items.id');

        $reviewCount = DB::table('product_reviews')
            ->selectRaw('COUNT(*)')
            ->whereColumn('product_reviews.inventory_item_id', 'inventory_items.id');

        return $this->query->newQuery()
            ->leftJoin('categories', 'categories.id', '=', 'inventory_items.category_id')
            ->select([
                'inventory_items.id',
                'inventory_items.title',
                'inventory_items.code',
                'inventory_items.unit',
                'inventory_items.price_per_unit',
                'inventory_items.compare_at_price',
                'inventory_items.image',
                'inventory_items.created_at',
                'categories.title as category_title',
                'categories.slug as category_slug',
            ])
            ->selectSub($netStock, 'stock_qty')
            ->selectSub($unitsSold, 'units_sold')
            ->selectSub($reviewAvg, 'review_avg')
            ->selectSub($reviewCount, 'review_count');
    }

    private function applyCategory(Builder $query, ?string $slug): void
    {
        if (filled($slug)) {
            $query->where('categories.slug', $slug);
        }
    }

    private function applySearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);
        if ($term === '') {
            return;
        }

        $query->where(function (Builder $q) use ($term) {
            $q->where('inventory_items.title', 'like', "%{$term}%")
                ->orWhere('inventory_items.code', 'like', "%{$term}%");
        });
    }

    private function applyPrice(Builder $query, $min, $max): void
    {
        if (is_numeric($min)) {
            $query->where('inventory_items.price_per_unit', '>=', (float) $min);
        }
        if (is_numeric($max)) {
            $query->where('inventory_items.price_per_unit', '<=', (float) $max);
        }
    }

    /**
     * Availability filters against net stock. Because stock is a subquery, it
     * has to be repeated in a HAVING - it is not a real column to filter on.
     *
     * @param array<int,string> $levels
     */
    private function applyAvailability(Builder $query, array $levels): void
    {
        $levels = array_intersect($levels, ['in', 'low', 'out']);
        if (empty($levels) || count($levels) === 3) {
            return;
        }

        $threshold = self::LOW_STOCK_THRESHOLD;
        $clauses = [];
        foreach ($levels as $level) {
            $clauses[] = match ($level) {
                'in' => "stock_qty > {$threshold}",
                'low' => "stock_qty > 0 AND stock_qty <= {$threshold}",
                'out' => 'stock_qty <= 0',
            };
        }

        $query->havingRaw('(' . implode(') OR (', $clauses) . ')');
    }

    private function applySort(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'price-asc' => $query->orderBy('inventory_items.price_per_unit'),
            'price-desc' => $query->orderByDesc('inventory_items.price_per_unit'),
            'newest' => $query->orderByDesc('inventory_items.id'),
            'best-selling' => $query
                ->orderByDesc('units_sold')
                ->orderByDesc('inventory_items.id'),
            // featured: in-stock first, then the best sellers, then newest
            default => $query
                ->orderByRaw('CASE WHEN stock_qty <= 0 THEN 1 ELSE 0 END')
                ->orderByDesc('units_sold')
                ->orderByDesc('inventory_items.id'),
        };
    }
}
