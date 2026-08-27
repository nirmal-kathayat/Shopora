<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryRecordRequest;
use App\Http\Requests\ProductRequest;
use App\Repository\CategoryRepository;
use App\Repository\InventoryRecordsRepository;
use App\Repository\ProductRepository;
use App\Repository\SupplierRepository;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productRepo, $categoryRepo, $supplierRepo, $records;
    public function __construct(ProductRepository $productRepo, CategoryRepository $categoryRepo, SupplierRepository $supplierRepo, InventoryRecordsRepository $records)
    {
        $this->productRepo = $productRepo;
        $this->categoryRepo = $categoryRepo;
        $this->supplierRepo = $supplierRepo;
        $this->records = $records;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $data = $this->productRepo->getProducts();
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->rawColumns([])
                    ->make(true);
            }
            return view('product.index');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
    public function create()
    {
        try {
            $categories = $this->categoryRepo->getCategory();
            $suppliers = $this->supplierRepo->getSupplier();
            return view('product.form')->with(['categories' => $categories, 'suppliers' => $suppliers]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function store(ProductRequest $request)
    {
        try {
            $data = $this->productRepo->store($request->validated());
            return redirect()->route('admin.product')->with(['message' => 'Product Added Successfully!', 'type' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            $categories = $this->categoryRepo->getCategory();
            $suppliers = $this->supplierRepo->getSupplier();
            $product = $this->productRepo->find($id);
            return view('product.form')->with(['product' => $product, 'categories' => $categories, 'suppliers' => $suppliers]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }


    public function update(ProductRequest $request, $id)
    {
        try {
            $this->productRepo->updateProduct($request->validated(), $id);
            return redirect()->route('admin.product')->with(['message' => 'product Updated Successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            $this->productRepo->delete($id);
            return redirect()->back()->with(['message' => 'product Deleted Successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function view($id)
    {
        try {
            $product = $this->productRepo->find($id);
            $inventoryRecords = $this->records->getInventoryRecords($id);
            return view('product.view')->with(['product' => $product, 'inventoryRecords' => $inventoryRecords]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }

    public function inventoryRecords(InventoryRecordRequest $request)
    {
        try {
            $data = $this->records->storeInventoryRecords($request->validated());
            return response()->json(['message' => 'Inventory Record Added Successfully!', 'type' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return response()->json(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
}
