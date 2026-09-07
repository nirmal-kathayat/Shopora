<?php

namespace App\Repository;

use App\Models\HeroSection;
use App\Repository\Concerns\StoresPublicImages;
use Illuminate\Support\Facades\DB;

class HeroSectionRepository
{
    use StoresPublicImages;

    private HeroSection $query;

    public function __construct(HeroSection $query)
    {
        $this->query = $query;
    }

    public function getHeroSections()
    {
        return $this->query
            ->newQuery()
            ->select([
                'id',
                'heading',
                'badge_text',
                'image',
                'primary_label',
                'status',
                'updated_at',
            ])
            ->orderByDesc('status')
            ->orderByDesc('id');
    }

    /**
     * The banner list, filtered and sorted for the table.
     *
     * The default order is the active one first - it is the banner the
     * storefront is actually serving, so it belongs at the top.
     */
    public function getHeroSectionsForListing(array $options = [])
    {
        $query = $this->getHeroSections()->reorder();

        $heading = trim((string) ($options['heading'] ?? ''));
        if ($heading !== '') {
            $query->where('heading', 'like', '%' . $heading . '%');
        }

        $status = trim((string) ($options['status'] ?? ''));
        if ($status === '0' || $status === '1') {
            $query->where('status', (int) $status);
        }

        // One box over the whole row: the headline, the little badge above it,
        // or the wording on the button.
        $search = trim((string) ($options['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('heading', 'like', $like)
                    ->orWhere('badge_text', 'like', $like)
                    ->orWhere('primary_label', 'like', $like);
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
        $sortable = ['heading', 'status', 'updated_at'];
        if (! in_array($field, $sortable, true)) {
            return $query->orderByDesc('status')->orderByDesc('id');
        }

        return $query->orderBy($field, strtolower((string) $direction) === 'asc' ? 'asc' : 'desc');
    }

    /** The active one, if the shop has made one active. */
    public function activeHeroSection(): ?HeroSection
    {
        return $this->query->newQuery()->active()->latest('id')->first();
    }

    public function find($id): HeroSection
    {
        return $this->query->newQuery()->findOrFail($id);
    }

    public function storeHeroSection(array $data): HeroSection
    {
        return DB::transaction(function () use ($data) {
            $hero = $this->query->newQuery()->create($this->attributes($data) + [
                'created_by' => $this->currentAdminId(),
            ]);

            $this->keepOnlyOneActive($hero);

            return $hero;
        });
    }

    public function updateHeroSection(array $data, int $id): HeroSection
    {
        return DB::transaction(function () use ($data, $id) {
            $hero = $this->find($id);
            $attributes = $this->attributes($data);

            // Leave a stored filename alone unless a new file came in.
            foreach (['image', 'author_image'] as $file) {
                if (! array_key_exists($file, $data)) {
                    unset($attributes[$file]);
                }
            }

            $attributes['updated_by'] = $this->currentAdminId();

            $hero->update($attributes);

            $this->keepOnlyOneActive($hero);

            return $hero->refresh();
        });
    }

    public function delete($id): void
    {
        DB::transaction(function () use ($id) {
            $hero = $this->find($id);

            $this->deleteImageFile($hero->image);
            $this->deleteImageFile($hero->author_image);
            $hero->delete();
        });
    }

    /**
     * Only one hero may be active. Activating this one turns the others off,
     * rather than leaving the storefront to guess between two.
     */
    private function keepOnlyOneActive(HeroSection $hero): void
    {
        if (! $hero->status) {
            return;
        }

        $this->query->newQuery()
            ->whereKeyNot($hero->getKey())
            ->where('status', true)
            ->update(['status' => false]);
    }

    private function currentAdminId(): ?int
    {
        return auth()->guard(config('permission.guard'))->id();
    }

    private function attributes(array $data): array
    {
        return [
            'badge_text' => $data['badge_text'] ?? null,
            'heading' => $data['heading'],
            'subheading' => $data['subheading'] ?? null,
            'author_name' => $data['author_name'] ?? null,
            'author_image' => $data['author_image'] ?? null,
            'primary_label' => $data['primary_label'] ?? null,
            'primary_url' => $data['primary_url'] ?? null,
            'secondary_label' => $data['secondary_label'] ?? null,
            'secondary_url' => $data['secondary_url'] ?? null,
            'image' => $data['image'] ?? null,
            'image_alt' => $data['image_alt'] ?? null,
            'popular_searches' => $data['popular_searches'] ?? [],
            'delivery_title' => $data['delivery_title'] ?? null,
            'delivery_subtitle' => $data['delivery_subtitle'] ?? null,
            'trust_label' => $data['trust_label'] ?? null,
            'trust_value' => $data['trust_value'] ?? null,
            'trust_subtitle' => $data['trust_subtitle'] ?? null,
            'status' => (bool) ($data['status'] ?? false),
        ];
    }

    protected function imagePrefix(): string
    {
        return 'hero';
    }
}
