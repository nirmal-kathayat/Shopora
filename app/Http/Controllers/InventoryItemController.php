<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryItemRequest;
use App\Models\InventoryItem;
use App\Models\StoreSetting;
use App\Repository\CategoryRepository;
use App\Repository\InventoryItemRepository;
use DataTables;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    private $inventoryItemRepo, $categoryRepo;

    public function __construct(InventoryItemRepository $inventoryItemRepo, CategoryRepository $categoryRepo)
    {
        $this->inventoryItemRepo = $inventoryItemRepo;
        $this->categoryRepo = $categoryRepo;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $categoryId = request()->input('category_id');
                $data = $this->inventoryItemRepo->getInventoryItems($categoryId);
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->rawColumns([])
                    ->make(true);
            }
            $categories = $this->categoryRepo->getCategory();

            return view('inventoryItem.index', compact('categories'));
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function getCategories()
    {
        try {
            $categories = $this->categoryRepo->getCategory();
            return response()->json([
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong!'], 500);
        }
    }

    public function create()
    {
        try {
            return view('inventoryItem.form', [
                'categories' => $this->categoryRepo->getCategory(),
                'trustBadges' => StoreSetting::productTrustBadges(),
                'trustIcons' => StoreSetting::TRUST_ICONS,
                'highlightIcons' => InventoryItem::HIGHLIGHT_ICONS,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.inventoryItem')
                ->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function store(InventoryItemRequest $request)
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = $this->inventoryItemRepo->storeImageFile($request->file('image'));
            }

            $item = $this->inventoryItemRepo->storeInventoryItem($data);

            if ($request->hasFile('gallery')) {
                $this->inventoryItemRepo->addGalleryImages($item->id, $request->file('gallery'));
            }

            $this->saveTrustBadges($request);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Inventory Item added successfully!',
                    'inventory' => $this->formatInventoryItem($item),
                ]);
            }

            return redirect()->route('admin.inventoryItem')->with(['message' => 'Inventory Item added successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['type' => 'error', 'message' => 'Something went wrong!'], 500);
            }
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            return view('inventoryItem.form', [
                'item' => $this->inventoryItemRepo->find($id),
                'categories' => $this->categoryRepo->getCategory(),
                'trustBadges' => StoreSetting::productTrustBadges(),
                'trustIcons' => StoreSetting::TRUST_ICONS,
                'highlightIcons' => InventoryItem::HIGHLIGHT_ICONS,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.inventoryItem')
                ->with(['message' => 'That inventory item no longer exists.', 'type' => 'error']);
        }
    }

    public function update(InventoryItemRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $existing = $this->inventoryItemRepo->find($id);

            if ($request->hasFile('image')) {
                $this->inventoryItemRepo->deleteImageFile($existing->image);
                $data['image'] = $this->inventoryItemRepo->storeImageFile($request->file('image'));
            } elseif ($request->boolean('remove_image')) {
                $this->inventoryItemRepo->deleteImageFile($existing->image);
                $data['image'] = null;
            }

            $this->inventoryItemRepo->updateInventoryItem($data, $id);

            if ($request->filled('remove_gallery')) {
                $this->inventoryItemRepo->removeGalleryImages((int) $id, (array) $request->input('remove_gallery'));
            }
            if ($request->hasFile('gallery')) {
                $this->inventoryItemRepo->addGalleryImages((int) $id, $request->file('gallery'));
            }

            $this->saveTrustBadges($request);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Inventory Item updated successfully!',
                ]);
            }

            return redirect()->route('admin.inventoryItem')->with(['message' => 'Inventory Item updated successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['type' => 'error', 'message' => 'Something went wrong!'], 500);
            }

            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            $this->inventoryItemRepo->delete($id);
            return redirect()->back()->with(['message' => 'Inventory Item deleted successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    /**
     * Trust badges are store-wide (the same on every product page), edited from
     * this form for convenience. Only saved when the form actually submitted
     * the fields, so the AJAX quick paths never wipe them.
     */
    private function saveTrustBadges(Request $request): void
    {
        if (! $request->has('badge_title')) {
            return;
        }

        StoreSetting::saveTrustBadgesFromRequest(
            (array) $request->input('badge_icon', []),
            (array) $request->input('badge_title', []),
            (array) $request->input('badge_subtitle', []),
        );
    }

    private function formatInventoryItem(InventoryItem $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'code' => $item->code,
            'unit' => $item->unit,
            'price_per_unit' => $item->price_per_unit,
            'category_id' => $item->category_id,
            'image' => $item->image,
            'image_url' => inventoryItemImageUrl($item->image),
        ];
    }
}
