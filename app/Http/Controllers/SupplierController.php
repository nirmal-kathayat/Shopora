<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Repository\SupplierRepository;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private $supplierRepo;

    public function __construct(SupplierRepository $supplierRepo)
    {
        $this->supplierRepo = $supplierRepo;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $suppliers = $this->supplierRepo->getSuppliers();
                return DataTables::of($suppliers)
                    ->addIndexColumn()
                    ->rawColumns([])
                    ->make(true);
            }
            return view('supplier.index');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }
    public function create()
    {
        try {
            return view('supplier.form');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function store(SupplierRequest $request)
    {
        try {
            $data = $this->supplierRepo->store($request->validated());
            return redirect()->route('admin.supplier')->with(['message' => 'Supplier added Successfully!', 'type' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            $supplier = $this->supplierRepo->find($id);
            return view('supplier.form')->with(['supplier' => $supplier]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function update(SupplierRequest $request, $id)
    {
        try {
            $this->supplierRepo->update($request->validated(), $id);
            return redirect()->route('admin.supplier')->with(['message' => 'Supplier Updated Successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            $this->supplierRepo->delete($id);
            return redirect()->back()->with(['message' => 'Supplier Deleted Successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }
}
