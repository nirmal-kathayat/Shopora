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

    /**
     * The bill list, and the JSON behind its table.
     *
     * A TableHelper, so the envelope is { success, data, total }. The bill
     * number is worked out here rather than in the page: the same helper the
     * bill itself uses, so the list and the bill cannot print two different
     * numbers for one sale.
     */
    public function index(Request $request)
    {
        try {
            if (! $request->ajax()) {
                // The bill resolves its own payment modes server-side now.
                return view('invoice.index');
            }

            $query = $this->invoiceRepo->getSalesInvoice([
                'order_by_name' => $request->input('order_by_name'),
                'customer_title' => $request->input('customer_title'),
                'search' => $request->input('search'),
                'sort_field' => $request->input('sort_field'),
                'sort_direction' => $request->input('sort_direction'),
            ]);

            $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
            $page = max((int) $request->input('page', 1), 1);

            $rows = $query->skip(($page - 1) * $perPage)->take($perPage)->get()
                ->map(function ($row) {
                    $row->bill_no = $this->invoiceRepo->billNumber((int) $row->id, $row->nepali_date);

                    return $row;
                });

            return response()->json([
                'success' => true,
                'data' => $rows,
                'total' => $query->getCountForPagination(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Invoice list failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Could not load',
                    'message' => 'The invoice list could not be loaded.',
                ]);
            }

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
