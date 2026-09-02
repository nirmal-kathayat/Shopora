<?php

namespace App\Repository;

use App\Models\DealCard;
use App\Models\DealSection;
use App\Repository\Concerns\StoresPublicImages;
use Illuminate\Support\Facades\DB;

class DealSectionRepository
{
    use StoresPublicImages;

    private DealSection $query;

    public function __construct(DealSection $query)
    {
        $this->query = $query;
    }

    public function getDealSections()
    {
        return $this->query
            ->newQuery()
            // select() before withCount(): the other way round, select()
            // replaces the count subquery and the column comes back missing.
            ->select(['id', 'heading', 'subheading', 'image', 'status', 'updated_at'])
            ->withCount('cards')
            ->orderByDesc('status')
            ->orderByDesc('id');
    }

    /** The active one, with its cards, ready for the storefront. */
    public function activeDealSection(): ?DealSection
    {
        return $this->query->newQuery()->with('cards')->active()->latest('id')->first();
    }

    public function find($id): DealSection
    {
        return $this->query->newQuery()->with('cards')->findOrFail($id);
    }

    /**
     * @param  array<int, array<string, mixed>>  $cards  already uploaded and normalised
     */
    public function storeDealSection(array $data, array $cards): DealSection
    {
        return DB::transaction(function () use ($data, $cards) {
            $section = $this->query->newQuery()->create($this->attributes($data) + [
                'created_by' => $this->currentAdminId(),
            ]);

            $this->syncCards($section, $cards);
            $this->keepOnlyOneActive($section);

            return $section;
        });
    }

    public function updateDealSection(array $data, array $cards, int $id): DealSection
    {
        return DB::transaction(function () use ($data, $cards, $id) {
            $section = $this->find($id);
            $attributes = $this->attributes($data);

            // Leave the stored filename alone unless a new file came in.
            if (! array_key_exists('image', $data)) {
                unset($attributes['image']);
            }

            $attributes['updated_by'] = $this->currentAdminId();
            $section->update($attributes);

            $this->syncCards($section, $cards);
            $this->keepOnlyOneActive($section);

            return $section->refresh();
        });
    }

    public function delete($id): void
    {
        DB::transaction(function () use ($id) {
            $section = $this->find($id);

            // The rows go with the cascade; the files on disk do not.
            foreach ($section->cards as $card) {
                $this->deleteImageFile($card->image);
            }
            $this->deleteImageFile($section->image);

            $section->delete();
        });
    }

    /**
     * Cards are edited inline on the section form, so this reconciles what was
     * submitted against what is stored: rows with an id are updated, rows
     * without one are created, and anything left out was removed by the shop.
     *
     * @param  array<int, array<string, mixed>>  $cards
     */
    private function syncCards(DealSection $section, array $cards): void
    {
        $keptIds = [];

        foreach (array_values($cards) as $position => $card) {
            $attributes = [
                'badge_text' => $card['badge_text'] ?? null,
                'title' => $card['title'],
                'description' => $card['description'] ?? null,
                'cta_label' => $card['cta_label'] ?? null,
                'cta_url' => $card['cta_url'] ?? null,
                'icon' => $card['icon'] ?? 'tag',
                'image_alt' => $card['image_alt'] ?? null,
                'featured' => (bool) ($card['featured'] ?? false),
                'sort_order' => $position,
            ];

            if (array_key_exists('image', $card)) {
                $attributes['image'] = $card['image'];
            }

            $existing = isset($card['id'])
                ? $section->cards()->whereKey($card['id'])->first()
                : null;

            if ($existing) {
                $existing->update($attributes);
                $keptIds[] = $existing->id;
                continue;
            }

            $keptIds[] = $section->cards()->create($attributes)->id;
        }

        $section->cards()
            ->whereKeyNot($keptIds ?: [0])
            ->get()
            ->each(function (DealCard $card) {
                $this->deleteImageFile($card->image);
                $card->delete();
            });

        $section->unsetRelation('cards');
    }

    /**
     * Only one section may be active. Activating this one turns the others
     * off, rather than leaving the storefront to guess between two.
     */
    private function keepOnlyOneActive(DealSection $section): void
    {
        if (! $section->status) {
            return;
        }

        $this->query->newQuery()
            ->whereKeyNot($section->getKey())
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
            'heading' => $data['heading'],
            'subheading' => $data['subheading'] ?? null,
            'image' => $data['image'] ?? null,
            'image_alt' => $data['image_alt'] ?? null,
            'trust_items' => $data['trust_items'] ?? [],
            'status' => (bool) ($data['status'] ?? false),
        ];
    }

    protected function imagePrefix(): string
    {
        return 'deal';
    }
}
