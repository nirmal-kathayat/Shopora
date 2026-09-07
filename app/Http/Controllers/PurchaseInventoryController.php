<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseInventoryRequest;
use App\Repository\CategoryRepository;
use App\Repository\InventoryItemRepository;
use App\Repository\PurchaseInventoryRepository;
use DataTables;
use Illuminate\Support\Facades\DB;

class PurchaseInventoryController extends Controller
{
    private $purchaseInventoryRepo, $inventoryItemRepo, $categoryRepo;
    public function __construct(PurchaseInventoryRepository $purchaseInventoryRepo, InventoryItemRepository $inventoryItemRepo, CategoryRepository $categoryRepo)
    {
        $this->purchaseInventoryRepo = $purchaseInventoryRepo;
        $this->inventoryItemRepo = $inventoryItemRepo;
        $this->categoryRepo = $categoryRepo;
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
                return view('purchaseInventory.index', [
                    'inventories' => $this->inventoryItemRepo->getInventoryTitle(),
                    'categories' => $this->categoryRepo->getCategory(),
                ]);
            }

            $perPage = min(max((int) request()->input('per_page', 10), 1), 100);
            $page = max((int) request()->input('page', 1), 1);

            $rows = $this->purchaseInventoryRepo->getPurchaseInventory([
                'vendor_name' => request()->input('vendor_name'),
                'search' => request()->input('search'),
                'sort_field' => request()->input('sort_field'),
                'sort_direction' => request()->input('sort_direction'),
            ])->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $rows->items(),
                'total' => $rows->total(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Purchase inventory list failed: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'The purchase list could not be loaded.',
                ]);
            }

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
    /**
     * Units on hand per item, and the JSON behind that table.
     *
     * A TableHelper, so the envelope is { success, data, total }. The query
     * groups by item, and a grouped query cannot be counted with count(*) -
     * that counts the groups' rows, not the groups - so the total is taken
     * from the query wrapped as a subquery.
     */
    public function storeDataDetails()
    {
        try {
            if (! request()->ajax()) {
                return view('storeRecords.index');
            }

            $perPage = min(max((int) request()->input('per_page', 10), 1), 100);
            $page = max((int) request()->input('page', 1), 1);

            $query = $this->purchaseInventoryRepo->getStoredRecords([
                'inventory_title' => request()->input('inventory_title'),
                'search' => request()->input('search'),
                'sort_field' => request()->input('sort_field'),
                'sort_direction' => request()->input('sort_direction'),
            ]);

            $total = DB::table(DB::raw('(' . $query->toSql() . ') as grouped'))
                ->mergeBindings($query)
                ->count();

            $rows = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'success' => true,
                'data' => $rows,
                'total' => $total,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Store records list failed: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'The record list could not be loaded.',
                ]);
            }

            return redirect()->back()->with(['type' => 'error', 'message' => 'Something went wrong!']);
        }
    }

    /**
     * One item's history: what came in, and what went out.
     *
     * Two TableHelpers on the page, so one endpoint answers both and `type`
     * says which. Envelope is { success, data, total } either way.
     */
    public function viewRecords($id)
    {
        try {
            if (! request()->ajax()) {
                return view('storeRecords.view', [
                    'id' => $id,
                    'item' => DB::table('inventory_items')->where('id', $id)->value('title'),
                ]);
            }

            $options = [
                'search' => request()->input('search'),
                'sort_field' => request()->input('sort_field'),
                'sort_direction' => request()->input('sort_direction'),
            ];

            $query = request()->input('type') === 'sales'
                ? $this->purchaseInventoryRepo->getSalesRecords($id, $options)
                : $this->purchaseInventoryRepo->getPurchaseRecords($id, $options);

            $perPage = min(max((int) request()->input('per_page', 10), 1), 100);
            $page = max((int) request()->input('page', 1), 1);

            return response()->json([
                'success' => true,
                'data' => $query->skip(($page - 1) * $perPage)->take($perPage)->get(),
                'total' => $query->getCountForPagination(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Store record view failed: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'These records could not be loaded.',
                ]);
            }

            return redirect()->back()->with(['type' => 'error', 'message' => 'Something went wrong!']);
        }
    }
}
