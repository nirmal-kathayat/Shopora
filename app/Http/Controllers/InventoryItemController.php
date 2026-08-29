<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryItemRequest;
use App\Models\InventoryItem;
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
        return redirect()->route('admin.inventoryItem');
    }

    public function store(InventoryItemRequest $request)
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = $this->inventoryItemRepo->storeImageFile($request->file('image'));
            }

            $item = $this->inventoryItemRepo->storeInventoryItem($data);

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
        return redirect()->route('admin.inventoryItem');
    }

    public function update(InventoryItemRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $existing = $this->inventoryItemRepo->find($id);

            if ($request->hasFile('image')) {
                $this->inventoryItemRepo->deleteImageFile($existing->image);
                $data['image'] = $this->inventoryItemRepo->storeImageFile($request->file('image'));
            }

            $this->inventoryItemRepo->updateInventoryItem($data, $id);

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
