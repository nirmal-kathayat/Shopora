<?php

namespace App\Http\Controllers;

use App\Http\Requests\HeroSectionRequest;
use App\Repository\HeroSectionRepository;

class HeroSectionController extends Controller
{
    private HeroSectionRepository $heroRepo;

    public function __construct(HeroSectionRepository $heroRepo)
    {
        $this->heroRepo = $heroRepo;
    }

    /**
     * The screen, and the JSON behind its table.
     *
     * A TableHelper, so the envelope is { success, data, total }.
     */
    public function index()
    {
        try {
            if (! request()->ajax()) {
                return view('heroSection.index');
            }

            $perPage = min(max((int) request()->input('per_page', 10), 1), 100);
            $page = max((int) request()->input('page', 1), 1);

            $rows = $this->heroRepo->getHeroSectionsForListing([
                'heading' => request()->input('heading'),
                'status' => request()->input('status'),
                'search' => request()->input('search'),
                'sort_field' => request()->input('sort_field'),
                'sort_direction' => request()->input('sort_direction'),
            ])->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => collect($rows->items())->map(fn ($hero) => [
                    'id' => $hero->id,
                    // The heading is written with line breaks and * markers for
                    // the storefront's own emphasis; the table wants one line.
                    'heading' => strip_tags(str_replace(["\r\n", "\n", '*'], [' ', ' ', ''], (string) $hero->heading)),
                    'badge_text' => $hero->badge_text,
                    'status' => (int) $hero->status,
                    'image_url' => $hero->image ? inventoryItemImageUrl($hero->image) : null,
                    'updated_at' => $hero->updated_at?->format('d M Y, g:i a'),
                ]),
                'total' => $rows->total(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Hero section list failed: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'The hero section list could not be loaded.',
                ]);
            }

            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function create()
    {
        try {
            return view('heroSection.form');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function store(HeroSectionRequest $request)
    {
        try {
            $data = $request->validated();

            foreach (['image', 'author_image'] as $file) {
                if ($request->hasFile($file)) {
                    $data[$file] = $this->heroRepo->storeImageFile($request->file($file));
                } else {
                    unset($data[$file]);
                }
            }

            try {
                $this->heroRepo->storeHeroSection($data);
            } catch (\Exception $e) {
                // Do not leave the just-uploaded files behind if the row never saved.
                $this->heroRepo->deleteImageFile($data['image'] ?? null);
                $this->heroRepo->deleteImageFile($data['author_image'] ?? null);
                throw $e;
            }

            return redirect()->route('admin.heroSection')
                ->with(['message' => 'Hero section added successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            $heroSection = $this->heroRepo->find($id);

            return view('heroSection.form')->with(['heroSection' => $heroSection]);
        } catch (\Exception $e) {
            return redirect()->route('admin.heroSection')
                ->with(['message' => 'That hero section no longer exists.', 'type' => 'error']);
        }
    }

    public function update(HeroSectionRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $existing = $this->heroRepo->find($id);

            foreach (['image' => 'remove_image', 'author_image' => 'remove_author_image'] as $file => $removeFlag) {
                if ($request->hasFile($file)) {
                    $this->heroRepo->deleteImageFile($existing->{$file});
                    $data[$file] = $this->heroRepo->storeImageFile($request->file($file));
                } elseif ($request->boolean($removeFlag)) {
                    $this->heroRepo->deleteImageFile($existing->{$file});
                    $data[$file] = null;
                } else {
                    // No key at all means "leave the stored filename alone".
                    unset($data[$file]);
                }
            }

            $this->heroRepo->updateHeroSection($data, (int) $id);

            return redirect()->route('admin.heroSection')
                ->with(['message' => 'Hero section updated successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            $this->heroRepo->delete($id);

            return redirect()->back()
                ->with(['message' => 'Hero section deleted successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
}
