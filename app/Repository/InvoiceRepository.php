<?php

namespace App\Repository;

use DB;
use App\Models\Sales;
use App\Models\StoreSetting;
use Carbon\Carbon;

class InvoiceRepository
{
    private $invoice;
    public function __construct(Sales $invoice)
    {
        $this->invoice = $invoice;
    }

    public function getSalesInvoice($filterType = null, $fromDate = null, $toDate = null)
    {
        if ($fromDate && $toDate) {
            $dateRange = [
                Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay()
            ];
        } else {
            $dateRange = $this->getDateRange($filterType ?? 'Monthly');
        }
        
        $data = DB::table('sales')
            ->leftJoin('admins', 'admins.id', '=', 'sales.order_by')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->whereNotIn('sales.status', ['cancelled', 'pending_payment'])
            ->select(
                'sales.*',
                DB::raw("COALESCE(admins.name, NULLIF(sales.order_by, '')) as order_by_name"),
                'customers.name as customer_title'
            )
            ->whereBetween('sales.created_at', $dateRange)
            ->orderBy('sales.id', 'desc');
        return $data;
    }

    public function getInvoiceById($id)
    {
        return DB::table('sales')
            ->leftJoin('admins', 'sales.order_by', '=', 'admins.id')
            ->select(
                'sales.*',
                DB::raw("COALESCE(admins.name, NULLIF(sales.order_by, '')) as order_by_name")
            )
            ->where('sales.id', $id)
            ->first();
    }

    /**
     * Everything one bill needs, assembled server-side.
     *
     * A counter sale and a storefront order are the same bill with different
     * parts filled in: the counter one is paid at the till and has no delivery,
     * the online one carries a delivery address and a delivery charge, and may
     * already be paid through eSewa. Working the totals out here rather than in
     * the page means the screen, the print-out and any future PDF cannot
     * disagree about what the customer owes.
     */
    public function getBill(int $id): ?array
    {
        $sale = DB::table('sales')
            ->leftJoin('admins', 'admins.id', '=', 'sales.order_by')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->select(
                'sales.*',
                DB::raw("COALESCE(admins.name, NULLIF(sales.order_by, '')) as order_by_name"),
                'customers.name as customer_name',
                'customers.ph_number as customer_phone',
                'customers.address as customer_address'
            )
            ->where('sales.id', $id)
            ->first();

        if (! $sale) {
            return null;
        }

        $items = DB::table('sales_products')
            ->join('inventory_items', 'sales_products.product_id', '=', 'inventory_items.id')
            ->where('sales_products.sales_id', $id)
            ->orderBy('sales_products.id')
            ->select(
                'inventory_items.title as item',
                'inventory_items.code',
                'inventory_items.unit',
                'sales_products.qty',
                'sales_products.price_per_unit as rate'
            )
            ->get()
            ->map(fn ($row) => [
                'item' => $row->item,
                'code' => $row->code,
                'unit' => $row->unit,
                'qty' => (int) $row->qty,
                'rate' => (float) $row->rate,
                'amount' => round((float) $row->rate * (int) $row->qty, 2),
            ])
            ->all();

        $subtotal = round(array_sum(array_column($items, 'amount')), 2);
        $discount = round((float) $sale->discount, 2);
        // The delivery charge is part of what was taken, so it belongs on the
        // bill. Leaving it off was quietly under-stating every online order.
        $delivery = round((float) ($sale->delivery_fee ?? 0), 2);
        $isOnline = ($sale->channel ?? 'counter') === 'storefront';

        return [
            'id' => (int) $sale->id,
            'bill_no' => $this->billNumber((int) $sale->id, $sale->nepali_date),
            'channel' => $isOnline ? 'storefront' : 'counter',
            'channel_label' => $isOnline ? 'Online order' : 'Counter sale',
            'status' => $sale->status,
            'date' => $sale->created_at,
            'nepali_date' => $sale->nepali_date,
            'served_by' => $isOnline ? null : $sale->order_by_name,
            'customer' => [
                'name' => $sale->customer_name ?: ($isOnline ? $sale->order_by_name : 'Walk-in customer'),
                'phone' => $sale->delivery_phone ?: $sale->customer_phone,
            ],
            // Only an online order has somewhere to be delivered to.
            'delivery' => $isOnline ? [
                'recipient' => $sale->delivery_recipient,
                'phone' => $sale->delivery_phone,
                'address' => $sale->delivery_address,
                'landmark' => $sale->delivery_landmark,
            ] : null,
            'payment' => $this->paymentSummary($sale, $isOnline),
            'items' => $items,
            'totals' => [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery' => $delivery,
                'grand_total' => round($subtotal - $discount + $delivery, 2),
            ],
            'shop' => StoreSetting::invoiceHeader(),
        ];
    }

    /**
     * "T95-83/84". The Nepali fiscal year runs 1 Shrawan to 31 Ashar, so a bill
     * dated in Baisakh, Jestha or Ashar still belongs to the year before.
     */
    private function billNumber(int $id, ?string $nepaliDate): string
    {
        $year = null;

        if ($nepaliDate && preg_match('/^(\d{4})-(\d{2})/', $nepaliDate, $m)) {
            $year = (int) $m[1];
            if ((int) $m[2] < 4) {
                $year--;
            }
        }

        if (! $year) {
            return 'T' . $id;
        }

        return sprintf('T%d-%02d/%02d', $id, $year % 100, ($year + 1) % 100);
    }

    /**
     * How the sale was paid for. An online order carries its own method and
     * status; a counter sale is settled at the till across one or more modes,
     * which is what sales_payment_mode records.
     */
    private function paymentSummary(object $sale, bool $isOnline): array
    {
        if ($isOnline) {
            $method = $sale->payment_method === 'esewa' ? 'eSewa' : 'Cash on Delivery';
            $status = match ($sale->payment_status) {
                'paid' => 'Paid',
                'failed' => 'Payment failed',
                default => $sale->payment_method === 'cod' ? 'Due on delivery' : 'Unpaid',
            };

            return [
                'method' => $method,
                'status' => $status,
                'is_paid' => $sale->payment_status === 'paid',
                'lines' => [['title' => $method, 'amount' => null]],
            ];
        }

        $lines = DB::table('sales_payment_mode')
            ->join('payment_modes', 'payment_modes.id', '=', 'sales_payment_mode.payment_mode_id')
            ->where('sales_payment_mode.sales_id', $sale->id)
            ->select('payment_modes.payment_title as title', 'sales_payment_mode.amount')
            ->get()
            ->map(fn ($row) => ['title' => $row->title, 'amount' => (float) $row->amount])
            ->all();

        return [
            // A split payment names every mode it was settled across.
            'method' => $lines ? implode(', ', array_column($lines, 'title')) : 'Cash',
            'status' => 'Paid at counter',
            'is_paid' => true,
            'lines' => $lines ?: [['title' => 'Cash', 'amount' => null]],
        ];
    }

    public function getInvoiceDetails($id)
    {
        return DB::table('sales_products')
            ->join('inventory_items', 'sales_products.product_id', '=', 'inventory_items.id')
            ->where('sales_products.sales_id', $id)
            ->select(
                'inventory_items.title as item',
                'sales_products.qty',
                'sales_products.payment_mode',
                'sales_products.price_per_unit as rate',
                DB::raw('sales_products.qty * sales_products.price_per_unit as amount')
            )
            ->get();
    }
    
    private function getDateRange($filterType)
    {
        switch ($filterType) {
            case 'Daily':
                return [
                    Carbon::today()->startOfDay(),
                    Carbon::today()->endOfDay()
                ];

            case 'Weekly':
                return [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ];

            case 'Monthly':
            default:
                return [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ];
        }
    }
}
