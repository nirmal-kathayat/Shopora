<?php

namespace App\Http\Controllers;

use App\Http\Requests\DealSectionRequest;
use App\Models\DealCard;
use App\Models\DealSection;
use App\Repository\DealSectionRepository;
use Yajra\DataTables\DataTables;

class DealSectionController extends Controller
{
    private DealSectionRepository $dealRepo;

    public function __construct(DealSectionRepository $dealRepo)
    {
        $this->dealRepo = $dealRepo;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $sections = $this->dealRepo->getDealSections();

                return DataTables::of($sections)
                    ->addIndexColumn()
                    ->editColumn('heading', fn ($section) => str_replace('*', '', (string) $section->heading))
                    ->editColumn('updated_at', fn ($section) => $section->updated_at?->format('d M Y, g:i a'))
                    ->addColumn('image_url', fn ($section) => inventoryItemImageUrl($section->image))
                    ->rawColumns([])
                    ->make(true);
            }

            return view('dealSection.index');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function create()
    {
        try {
            return view('dealSection.form', $this->formOptions());
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function store(DealSectionRequest $request)
    {
        $uploaded = [];

        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $uploaded[] = $this->dealRepo->storeImageFile($request->file('image'));
            } else {
                unset($data['image']);
            }

            $cards = $this->cardsWithImages($request, $uploaded);

            $this->dealRepo->storeDealSection($data, $cards);

            return redirect()->route('admin.dealSection')
                ->with(['message' => 'Deals section added successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            // Do not leave just-uploaded files behind if nothing was saved.
            foreach ($uploaded as $filename) {
                $this->dealRepo->deleteImageFile($filename);
            }

            return redirect()->back()->withInput()
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            $dealSection = $this->dealRepo->find($id);

            return view('dealSection.form', $this->formOptions() + ['dealSection' => $dealSection]);
        } catch (\Exception $e) {
            return redirect()->route('admin.dealSection')
                ->with(['message' => 'That deals section no longer exists.', 'type' => 'error']);
        }
    }

    public function update(DealSectionRequest $request, $id)
    {
        $uploaded = [];

        try {
            $data = $request->validated();
            $existing = $this->dealRepo->find($id);

            if ($request->hasFile('image')) {
                $this->dealRepo->deleteImageFile($existing->image);
                $data['image'] = $this->dealRepo->storeImageFile($request->file('image'));
            } elseif ($request->boolean('remove_image')) {
                $this->dealRepo->deleteImageFile($existing->image);
                $data['image'] = null;
            } else {
                // No key at all means "leave the stored filename alone".
                unset($data['image']);
            }

            $cards = $this->cardsWithImages($request, $uploaded, $existing);

            $this->dealRepo->updateDealSection($data, $cards, (int) $id);

            return redirect()->route('admin.dealSection')
                ->with(['message' => 'Deals section updated successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            foreach ($uploaded as $filename) {
                $this->dealRepo->deleteImageFile($filename);
            }

            return redirect()->back()->withInput()
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            $this->dealRepo->delete($id);

            return redirect()->back()
                ->with(['message' => 'Deals section deleted successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    /**
     * Turn the submitted card rows into what the repository stores, moving any
     * uploaded file into place first. A row with no new file and no removal
     * request simply omits 'image', which leaves the stored one alone.
     *
     * @param  array<int, string>  $uploaded  filenames written, for cleanup on failure
     */
    private function cardsWithImages($request, ?array &$uploaded = null, ?DealSection $existing = null): array
    {
        $uploaded ??= [];
        $storedById = $existing
            ? $existing->cards->keyBy('id')
            : collect();

        $cards = [];
        foreach ($request->cardRows() as $key => $row) {
            $card = [
                'id' => $row['id'] ?? null,
                'badge_text' => $row['badge_text'] ?? null,
                'title' => $row['title'] ?? '',
                'description' => $row['description'] ?? null,
                'cta_label' => $row['cta_label'] ?? null,
                'cta_url' => $row['cta_url'] ?? null,
                'icon' => $row['icon'] ?? 'tag',
                'image_alt' => $row['image_alt'] ?? null,
                'featured' => (bool) ($row['featured'] ?? false),
            ];

            $file = $request->file("cards.$key.image");
            $stored = $card['id'] ? $storedById->get($card['id']) : null;

            if ($file) {
                $this->dealRepo->deleteImageFile($stored?->image);
                $card['image'] = $uploaded[] = $this->dealRepo->storeImageFile($file);
            } elseif (filter_var($row['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $this->dealRepo->deleteImageFile($stored?->image);
                $card['image'] = null;
            }

            $cards[] = $card;
        }

        return $cards;
    }

    /** Icon choices shared by the create and edit forms. */
    private function formOptions(): array
    {
        return [
            'cardIcons' => DealCard::ICONS,
            'trustIcons' => DealSection::TRUST_ICONS,
        ];
    }
}
