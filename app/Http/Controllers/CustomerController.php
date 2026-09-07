<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Repository\CustomerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    private $customerRepo;
    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepo = $customerRepo;
    }

    /**
     * The screen, and the JSON behind its table.
     *
     * A TableHelper, so the envelope is { success, data, total }. The query
     * carries a selectSub for the address count, and getCountForPagination()
     * hands back an object rather than a number on one of those - so the
     * total is taken from the query wrapped as a subquery.
     */
    public function index()
    {
        try {
            if (! request()->ajax()) {
                return view('customer.index');
            }

            $perPage = min(max((int) request()->input('per_page', 10), 1), 100);
            $page = max((int) request()->input('page', 1), 1);

            $query = $this->customerRepo->getCustomersForListing([
                'name' => request()->input('name'),
                'email' => request()->input('email'),
                'ph_number' => request()->input('ph_number'),
                'is_registered' => request()->input('is_registered'),
                'search' => request()->input('search'),
                'sort_field' => request()->input('sort_field'),
                'sort_direction' => request()->input('sort_direction'),
            ]);

            $base = $query->toBase();
            $total = DB::table(DB::raw('(' . $base->toSql() . ') as counted'))
                ->mergeBindings($base)
                ->count();

            $rows = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'success' => true,
                'data' => $rows->map(fn ($customer) => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'ph_number' => $customer->ph_number,
                    'address' => $customer->address,
                    'pan_number' => $customer->pan_number,
                    'is_registered' => (int) $customer->is_registered,
                    'address_count' => (int) $customer->address_count,
                    'created_at' => $customer->created_at?->format('d M Y'),
                ]),
                'total' => $total,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Customer list failed: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'The customer list could not be loaded.',
                ]);
            }

            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    /**
     * Every delivery address this customer keeps. The list column shows the
     * default one; this is the rest, for the modal on the customer list.
     */
    public function addresses($id)
    {
        try {
            $customer = $this->customerRepo->find($id);

            return response()->json([
                'customer' => $customer->name,
                'addresses' => $customer->addresses()
                    ->orderByDesc('is_default')
                    ->orderByDesc('id')
                    ->get()
                    ->map(fn ($address) => [
                        'label' => $address->label,
                        'recipient_name' => $address->recipient_name,
                        'ph_number' => $address->ph_number,
                        'single_line' => $address->single_line,
                        'landmark' => $address->landmark,
                        'is_default' => (bool) $address->is_default,
                    ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong!'], 500);
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
