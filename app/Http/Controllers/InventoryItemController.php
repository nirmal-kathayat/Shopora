<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryItemRequest;
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
            return view('inventoryItem.index');
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
            $categories = $this->categoryRepo->getCategory();
            $inventoryCount = $this->inventoryItemRepo->countInventories();
            return view('inventoryItem.form')->with(['categories' => $categories, 'inventoryCount' => $inventoryCount]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function store(InventoryItemRequest $request)
    {
        // dd($request->all());
        try {
            $item = $this->inventoryItemRepo->storeInventoryItem($request->validated());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Inventory Item added successfully!',
                    'inventory' => [
                        'id' => $item->id,
                        'title' => $item->title,
                        'code' => $item->code,
                        'unit' => $item->unit,
                        'price_per_unit' => $item->price_per_unit,
                        'category_id' => $item->category_id,
                    ],
                ]);
            }

            return redirect()->route('admin.inventoryItem')->with(['message' => 'Inventory Item added successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['type' => 'error', 'message' => 'Something went wrong!'], 500);
            }
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            $categories = $this->categoryRepo->getCategory();
            $inventoryItem = $this->inventoryItemRepo->find($id);
            return view('inventoryItem.form')->with(['categories' => $categories, 'inventoryItem' => $inventoryItem]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function update(InventoryItemRequest $request, $id)
    {
      
        try {
            $this->inventoryItemRepo->updateInventoryItem($request->validated(), $id);
            return redirect()->route('admin.inventoryItem')->with(['message' => 'Inventory Item updated successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
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
}
