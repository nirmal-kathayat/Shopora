<?php

namespace App\Repository;

use App\Models\HeroSection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class HeroSectionRepository
{
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

            // Leave the stored filename alone unless a new file came in.
            if (! array_key_exists('image', $data)) {
                unset($attributes['image']);
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

    public function storeImageFile(UploadedFile $file): string
    {
        $directory = public_path('image');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = 'hero_' . time() . '_' . uniqid() . '.' . $extension;
        $file->move($directory, $filename);

        return $filename;
    }

    public function deleteImageFile(?string $filename): void
    {
        if (empty($filename)) {
            return;
        }

        $path = public_path('image/' . ltrim($filename, '/'));
        if (is_file($path)) {
            unlink($path);
        }
    }
}
