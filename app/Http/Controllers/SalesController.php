<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesRequest;
use App\Repository\CategoryRepository;
use App\Repository\CustomerRepository;
use App\Repository\InventoryItemRepository;
use App\Repository\SalesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    private $inventoryItemRepo, $categoryRepo, $salesRepo, $customerRepo;

    public function __construct(InventoryItemRepository $inventoryItemRepo, CategoryRepository $categoryRepo, SalesRepository $salesRepo, CustomerRepository $customerRepo)
    {
        $this->inventoryItemRepo = $inventoryItemRepo;
        $this->categoryRepo = $categoryRepo;
        $this->salesRepo = $salesRepo;
        $this->customerRepo = $customerRepo;
    }

    /**
     * How many items the counter's list holds at once. Enough for a mart's
     * whole catalogue; the search and the category filter are what narrow a
     * longer one.
     */
    private const COUNTER_LIST_LIMIT = 50;

    public function index(Request $request)
    {
        try {
            $categories = $this->categoryRepo->getCategory();
            $customers = $this->customerRepo->getCustomers();
            $term = $request->input('search');
            $paymentModes = DB::table('payment_modes')->get();
            // The counter's item list. The category is filtered here rather
            // than in the browser: the page only ever holds a slice of the
            // catalogue, so hiding rows client-side could only ever filter
            // what happened to be loaded - pick a category whose items were
            // not in that slice and the list came up empty.
            $inventories = $this->inventoryItemRepo->getInventoryItems($request->input('category_id'))
                ->select(
                    'inventory_items.id',
                    'inventory_items.title',
                    'inventory_items.code',
                    'inventory_items.unit',
                    'inventory_items.price_per_unit',
                    'inventory_items.category_id',
                    'inventory_items.image'
                );

            // Grouped, or the OR would reach back past the category filter and
            // match a code in every aisle.
            if ($term) {
                $inventories->where(function ($query) use ($term) {
                    $query->where('inventory_items.title', 'LIKE', "%{$term}%")
                        ->orWhere('inventory_items.code', 'LIKE', "%{$term}%");
                });
            }

            $inventories = $inventories->limit(self::COUNTER_LIST_LIMIT)->get();
            if ($request->ajax()) {
                $inventories = $inventories->map(function ($item) {
                    $item->image_url = inventoryItemImageUrl($item->image);

                    return $item;
                });

                return response()->json([
                    'inventories' => $inventories
                ]);
            }
            return view('sales.index', ['inventories' => $inventories, 'categories' => $categories, 'customers' => $customers, 'paymentModes' => $paymentModes]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function storeSales(SalesRequest $request)
    {
        // dd($request->all());
        try {
            $data = $this->salesRepo->storeSalesProduct($request->validated());
            return response()->json(['message' => 'Sales order added successfully!', 'type' => 'success', 'invoice_id' => $data->id]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q');
        $customersQuery = $this->customerRepo->getCustomers()->select('id', 'name', 'ph_number');

        // If no search term, return a small default list (2-4 recent customers)
        if (!$term) {
            $customers = $customersQuery->limit(4)->get();
            return response()->json($customers);
        }

        $customers = $customersQuery
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('ph_number', 'LIKE', "%{$term}%");
            })
            ->limit(20)
            ->get();
        return response()->json($customers);
    }
}
