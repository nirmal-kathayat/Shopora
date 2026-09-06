<?php

namespace App\Http\Controllers;

use App\Repository\InvoiceRepository;
use Illuminate\Http\Request;
use DataTables;

class InvoiceController extends Controller
{
    private $invoiceRepo;

    public function __construct(InvoiceRepository $invoiceRepo)
    {
        $this->invoiceRepo = $invoiceRepo;
    }

    public function index(Request $request)
    {
        try {
            if (request()->ajax()) {
                $fromDate = $request->get('from_date');
                $toDate = $request->get('to_date');
                $data = $this->invoiceRepo->getSalesInvoice(null, $fromDate, $toDate);
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->filterColumn('order_by_name', function ($query, $keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('admins.name', 'like', "%{$keyword}%")
                                ->orWhere('sales.order_by', 'like', "%{$keyword}%");
                        });
                    })
                    ->rawColumns([])
                    ->make(true);
            }
            // The bill resolves its own payment modes server-side now.
            return view('invoice.index');
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
    /**
     * One bill, whichever way the sale was made. The Sales, Invoice and
     * Dashboard screens all read this, so the counter and the storefront can
     * never end up showing two different bills for the same money.
     */
    public function viewInvoice($id)
    {
        try {
            $bill = $this->invoiceRepo->getBill((int) $id);

            if (! $bill) {
                return response()->json(['error' => 'Invoice not found.'], 404);
            }

            return response()->json(['bill' => $bill]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }
}
