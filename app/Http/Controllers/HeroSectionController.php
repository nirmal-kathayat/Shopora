<?php

namespace App\Http\Controllers;

use App\Http\Requests\HeroSectionRequest;
use App\Repository\HeroSectionRepository;
use Yajra\DataTables\DataTables;

class HeroSectionController extends Controller
{
    private HeroSectionRepository $heroRepo;

    public function __construct(HeroSectionRepository $heroRepo)
    {
        $this->heroRepo = $heroRepo;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $heroes = $this->heroRepo->getHeroSections();

                return DataTables::of($heroes)
                    ->addIndexColumn()
                    ->editColumn('heading', fn ($hero) => strip_tags(str_replace(["\r\n", "\n", '*'], [' ', ' ', ''], $hero->heading)))
                    ->editColumn('updated_at', fn ($hero) => $hero->updated_at?->format('d M Y, g:i a'))
                    ->addColumn('image_url', fn ($hero) => inventoryItemImageUrl($hero->image))
                    ->rawColumns([])
                    ->make(true);
            }

            return view('heroSection.index');
        } catch (\Exception $e) {
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
