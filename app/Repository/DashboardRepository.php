<?php

namespace App\Repository;

use DB;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Sales;
use App\Models\SalesProduct;
use Carbon\Carbon;

class DashboardRepository
{
    public function getFilteredData($filterType = null, $fromDate = null, $toDate = null)
    {
        if ($fromDate && $toDate) {
            $dateRange = [
                Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay()
            ];
        } else {
            $dateRange = $this->getDateRange($filterType ?? 'Monthly');
        }
        
        $salesCount = Sales::whereBetween('created_at', $dateRange)->count();
        $totalRevenue = 0;
        if ($salesCount > 0) {
            // Calculate revenue based on sales table with discounts applied
            $salesData = Sales::whereBetween('created_at', $dateRange)
                ->select('id')
                ->get();
            
            foreach ($salesData as $sale) {
                // Calculate total for this sale (items total - discount)
                $itemsTotal = SalesProduct::where('sales_id', $sale->id)
                    ->sum(DB::raw('price_per_unit * qty'));
                $discount = Sales::where('id', $sale->id)->value('discount') ?? 0;
                $totalRevenue += max($itemsTotal - $discount, 0);
            }
        }

        return [
            'totalInventoryItems' => InventoryItem::count(),
            'totalCustomer' => Customer::count(),
            'totalRevenue' => $totalRevenue,
            'totalSales' => $salesCount
        ];
    }

    public function getPaymentMethodRevenue($filterType = null, $fromDate = null, $toDate = null)
    {
        if ($fromDate && $toDate) {
            $dateRange = [
                Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay()
            ];
        } else {
            $dateRange = $this->getDateRange($filterType ?? 'Monthly');
        }
        
        $data = DB::table('sales_payment_mode')
            ->join('payment_modes', 'sales_payment_mode.payment_mode_id', '=', 'payment_modes.id')
            ->join('sales', 'sales_payment_mode.sales_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', $dateRange)
            ->select(
                'payment_modes.payment_title',
                DB::raw('SUM(sales_payment_mode.amount) as total_amount')
            )
            ->groupBy('payment_modes.payment_title')
            ->orderBy('total_amount', 'desc')
            ->get();
            $totalRevenue = $data->sum('total_amount');

            $totalDiscount = Sales::whereBetween('created_at', $dateRange)
            ->sum('discount');
            $netRevenue = $totalRevenue - $totalDiscount;
    
        return [
            'payment_modes'  => $data,
            'total_revenue'  => $totalRevenue,
            'total_discount' => $totalDiscount,
            'net_revenue'    => $netRevenue,
        ];

        return $result;
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
