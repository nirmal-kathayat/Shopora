<?php

namespace App\Repository;

use DB;
use App\Models\Sales;
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
            ->select('sales.*', 'admins.name as order_by_name', 'customers.name as customer_title')
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
                'admins.name as order_by_name'
            )
            ->where('sales.id', $id)
            ->first();
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
