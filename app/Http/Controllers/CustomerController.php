<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Repository\CustomerRepository;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private $customerRepo;
    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepo = $customerRepo;
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $customer = $this->customerRepo->getCustomers();
                return DataTables::of($customer)
                    ->addIndexColumn()
                    ->editColumn('created_at', fn ($customer) => $customer->created_at?->format('d M Y'))
                    ->rawColumns([])
                    ->make(true);
            }
            return view('customer.index');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }
    public function create()
    {
        try {
            return view('customer.form');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function store(CustomerRequest $request)
    {
        try {
            $data = $this->customerRepo->storeCustomer($request->validated());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Customer added successfully!',
                    'id' => $data->id,
                    'name' => $data->name,
                    'ph_number' => $data->ph_number,
                ]);
            }

            return redirect()->route('admin.customer')->with(['message' => 'Customer added successfully!', 'type' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['type' => 'error', 'message' => 'Something went wrong'], 500);
            }
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function edit($id)
    {
        try {
            $customer = $this->customerRepo->find($id);
            return view('customer.form')->with(['customer' => $customer]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function update(CustomerRequest $request, $id)
    {
        try {
            $this->customerRepo->updateCustomer($request->validated(), $id);
            return redirect()->route('admin.customer')->with(['message' => 'Customer updated successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function delete($id)
    {
        try {
            $this->customerRepo->delete($id);
            return redirect()->back()->with(['message' => 'Customer deleted successfully!', 'type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }
}
