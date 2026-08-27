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
                    ->rawColumns([])
                    ->make(true);
            }
            $paymentModes = \DB::table('payment_modes')->get();
            return view('invoice.index', ['paymentModes' => $paymentModes]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong!', 'type' => 'error']);
        }
    }
    public function viewInvoice($id)
    {
        try {
            $invoice = $this->invoiceRepo->getInvoiceById($id);
            $details = $this->invoiceRepo->getInvoiceDetails($id);
            return response()->json([
                'invoice' => $invoice,
                'details' => $details
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }
}
