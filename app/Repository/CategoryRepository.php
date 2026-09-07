<?php

namespace App\Repository;

use App\Models\Category;
use App\Repository\Concerns\StoresPublicImages;
use Illuminate\Support\Str;

class CategoryRepository
{
    use StoresPublicImages;

    private Category $query;

    public function __construct(Category $query)
    {
        $this->query = $query;
    }

    /** For the inventory dropdowns - just enough to pick one. */
    public function getCategory()
    {
        return $this->query->newQuery()
            ->select('id', 'title')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    /**
     * The admin listing, with how many items sit in each category.
     *
     * Sort order is the shop's own arrangement of its aisles, so that is what
     * the list opens on; anything else is the reader asking a question.
     */
    public function getCategoriesForListing(array $options = [])
    {
        $query = $this->query->newQuery()
            ->select(['id', 'title', 'slug', 'icon', 'image', 'status', 'sort_order', 'updated_at'])
            ->withCount('inventoryItems');

        // Header-row filters: one box per column, each its own LIKE.
        foreach (['title', 'slug'] as $column) {
            $value = trim((string) ($options[$column] ?? ''));
            if ($value !== '') {
                $query->where($column, 'like', '%' . $value . '%');
            }
        }

        $search = trim((string) ($options['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('title', 'like', $like)->orWhere('slug', 'like', $like);
            });
        }

        return $this->sortListing($query, $options['sort_field'] ?? null, $options['sort_direction'] ?? null);
    }

    /**
     * Order the listing. By column name only - the field arrives in a query
     * string, and a column name is not something to take on trust.
     */
    private function sortListing($query, $field, $direction)
    {
        $sortable = ['sort_order', 'title', 'slug', 'status', 'inventory_items_count'];
        $dir = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';

        if (! in_array($field, $sortable, true)) {
            return $query->orderBy('sort_order')->orderBy('id');
        }

        return $query->orderBy($field, $dir);
    }

    /** Active categories with their item counts, for the storefront. */
    public function getActiveCategories()
    {
        return $this->query->newQuery()
            ->active()
            ->withCount('inventoryItems')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    public function find($id): Category
    {
        return $this->query->newQuery()->findOrFail($id);
    }

    /**
     * The inventory modal's quick-add, which posts only a title. Everything
     * else takes a sensible default so the row is still storefront-ready.
     */
    public function store(array $data): Category
    {
        return $this->query->newQuery()->create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['title']),
            'icon' => $data['icon'] ?? 'grid',
            'image' => $data['image'] ?? null,
            'image_alt' => $data['image_alt'] ?? null,
            'status' => array_key_exists('status', $data) ? (bool) $data['status'] : true,
            'sort_order' => $data['sort_order'] ?? $this->nextSortOrder(),
            'created_by' => $this->currentAdminId(),
        ]);
    }

    public function update(array $data, int $id): Category
    {
        $category = $this->find($id);

        $attributes = [
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['title'], $category->id),
            'icon' => $data['icon'] ?? $category->icon,
            'image_alt' => $data['image_alt'] ?? null,
            'status' => array_key_exists('status', $data) ? (bool) $data['status'] : $category->status,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
            'updated_by' => $this->currentAdminId(),
        ];

        // Leave the stored filename alone unless the caller decided on one.
        if (array_key_exists('image', $data)) {
            $attributes['image'] = $data['image'];
        }

        $category->update($attributes);

        return $category->refresh();
    }

    public function delete(int $id): void
    {
        $category = $this->find($id);

        $this->deleteImageFile($category->image);
        $category->delete();
    }

    /** How many items would be orphaned by deleting this category. */
    public function itemCount(int $id): int
    {
        return $this->find($id)->inventoryItems()->count();
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (
            $this->query->newQuery()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function nextSortOrder(): int
    {
        return (int) $this->query->newQuery()->max('sort_order') + 1;
    }

    private function currentAdminId(): ?int
    {
        return auth()->guard(config('permission.guard'))->id();
    }

    protected function imagePrefix(): string
    {
        return 'category';
    }
}
