<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseInventoryRequest;
use App\Repository\CategoryRepository;
use App\Repository\InventoryItemRepository;
use App\Repository\PurchaseInventoryRepository;
use DataTables;

class PurchaseInventoryController extends Controller
{
    private $purchaseInventoryRepo, $inventoryItemRepo, $categoryRepo;
    public function __construct(PurchaseInventoryRepository $purchaseInventoryRepo, InventoryItemRepository $inventoryItemRepo, CategoryRepository $categoryRepo)
    {
        $this->purchaseInventoryRepo = $purchaseInventoryRepo;
        $this->inventoryItemRepo = $inventoryItemRepo;
        $this->categoryRepo = $categoryRepo;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $data = $this->purchaseInventoryRepo->getPurchaseInventory();
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->rawColumns([])
                    ->make(true);
            }
            $inventories = $this->inventoryItemRepo->getInventoryTitle();
            $categories = $this->categoryRepo->getCategory();

            return view('purchaseInventory.index', compact('inventories', 'categories'));
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
    public function create()
    {
        return redirect()->route('admin.purchaseInventory');
    }

    public function store(PurchaseInventoryRequest $request)
    {
        // dd($request->all());
        try {
            $this->purchaseInventoryRepo->store($request->validated());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Purchase inventory added!',
                ]);
            }

            return redirect()->route('admin.purchaseInventory')->with(['message' => 'Purchase inventory added!', 'type' => 'success']);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['type' => 'error', 'message' => 'Something went wrong!'], 500);
            }

            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        return redirect()->route('admin.purchaseInventory');
    }

    public function update(PurchaseInventoryRequest $request, $id)
    {
        try {
            $this->purchaseInventoryRepo->update($request->validated(), $id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Purchase inventory updated!',
                ]);
            }

            return redirect()->route('admin.purchaseInventory')->with(['message' => 'Purchase inventory updated!', 'type' => 'success']);
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
            $this->purchaseInventoryRepo->delete($id);
            return redirect()->back()->with(['message' => 'Purchase inventory deleted!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
    public function view($id)
    {
        try {
            $purchaseInventory = $this->purchaseInventoryRepo->find($id);
            return response()->json($purchaseInventory);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }
    // store record details
    public function storeDataDetails()
    {
        try {
            if (request()->ajax()) {
                $data = $this->purchaseInventoryRepo->getStoredRecords();
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->rawColumns([])
                    ->make(true);
            }
            return view('storeRecords.index');
        } catch (\Exception $e) {
            return redirect()->back()->with(['type' => 'error', 'message' => 'Something went wrong!']);
        }
    }

    public function viewRecords($id)
    {
        try {
            if (request()->ajax()) {
                $type = request()->get('type');

                if ($type === 'purchase') {
                    $records = $this->purchaseInventoryRepo->getPurchaseRecords($id);
                } else if ($type === 'sales') {
                    $records = $this->purchaseInventoryRepo->getSalesRecords($id);
                }

                return DataTables::of($records)
                    ->addIndexColumn()
                    ->rawColumns([])
                    ->make(true);
            }

            return view('storeRecords.view', ['id' => $id]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['type' => 'error', 'message' => 'Something went wrong!']);
        }
    }
}
