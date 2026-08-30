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
    private const LOW_STOCK_THRESHOLD = 10;

    public function getFilteredData($filterType = null, $fromDate = null, $toDate = null)
    {
        [$from, $to] = $this->resolveDateRange($filterType, $fromDate, $toDate);

        $dateRange = [$from, $to];
        $periodDays = max($from->diffInDays($to), 0);
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($periodDays)->startOfDay();
        $prevRange = [$prevFrom, $prevTo];

        $current = $this->computePeriodStats($dateRange);
        $previous = $this->computePeriodStats($prevRange);

        $costMap = $this->itemCostMap();
        $cogs = $this->computeCogs($dateRange, $costMap);
        $prevCogs = $this->computeCogs($prevRange, $costMap);
        $grossProfit = $current['revenue'] - $cogs;
        $prevProfit = $previous['revenue'] - $prevCogs;
        $profitMargin = $current['revenue'] > 0 ? round($grossProfit / $current['revenue'] * 100, 1) : 0.0;

        $totalPurchases = $this->purchaseCostForRange($dateRange);
        $prevPurchases = $this->purchaseCostForRange($prevRange);

        $inventory = $this->inventoryValuation();
        $invoiceCount = (int) Sales::whereBetween('created_at', $dateRange)->count();
        $purchaseCount = (int) DB::table('purchase_inventory')->whereBetween('created_at', $dateRange)->count();
        $topVendor = $this->topVendorForRange($dateRange);

        return [
            'totalInventoryItems' => InventoryItem::count(),
            'totalCustomer' => Customer::count(),
            'totalRevenue' => $current['revenue'],
            'totalSales' => $current['salesQty'],
            'invoiceCount' => $invoiceCount,
            'avgOrderValue' => $invoiceCount > 0 ? round($current['revenue'] / $invoiceCount, 2) : 0,
            'outOfStock' => $this->outOfStockCount(),
            'purchaseCount' => $purchaseCount,
            'avgPurchase' => $purchaseCount > 0 ? round($totalPurchases / $purchaseCount, 2) : 0,
            'topVendorName' => $topVendor['name'],
            'topVendorAmount' => $topVendor['total'],
            'stockAlerts' => $this->getStockAlertCount(),
            'grossProfit' => $grossProfit,
            'profitMargin' => $profitMargin,
            'profitChangePercent' => $this->percentChange($grossProfit, $prevProfit),
            'totalPurchases' => $totalPurchases,
            'purchasesChangePercent' => $this->percentChange($totalPurchases, $prevPurchases),
            'inventoryValue' => $inventory['value'],
            'inventoryUnits' => $inventory['units'],
            'itemsChangePercent' => $this->percentChange(
                InventoryItem::where('created_at', '<=', $to)->count(),
                InventoryItem::where('created_at', '<=', $prevTo)->count()
            ),
            'revenueChangePercent' => $this->percentChange($current['revenue'], $previous['revenue']),
            'salesChangePercent' => $this->percentChange($current['salesQty'], $previous['salesQty']),
            'recentSales' => $this->getRecentSales($dateRange, 5),
            'lowStockItems' => $this->getTopLowStockItems(5),
            'bestSellers' => $this->getBestSellers($dateRange, 5),
            'topCustomers' => $this->getTopCustomers($dateRange, 5),
            'recentPurchases' => $this->getRecentPurchases($dateRange, 5),
        ];
    }

    private function itemCostMap(): array
    {
        return DB::table('purchase_inventory_items')
            ->select('inventory_item_id', DB::raw('AVG(rate) as avg_rate'))
            ->groupBy('inventory_item_id')
            ->pluck('avg_rate', 'inventory_item_id')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    private function computeCogs(array $dateRange, ?array $costMap = null): float
    {
        $costMap = $costMap ?? $this->itemCostMap();

        $rows = DB::table('sales_products')
            ->join('sales', 'sales.id', '=', 'sales_products.sales_id')
            ->whereBetween('sales.created_at', $dateRange)
            ->select('sales_products.product_id', DB::raw('SUM(sales_products.qty) as qty'))
            ->groupBy('sales_products.product_id')
            ->get();

        $cogs = 0.0;
        foreach ($rows as $row) {
            $cogs += ($costMap[$row->product_id] ?? 0) * (int) $row->qty;
        }

        return $cogs;
    }

    private function purchaseCostForRange(array $dateRange): float
    {
        return (float) DB::table('purchase_inventory_items')
            ->join('purchase_inventory', 'purchase_inventory.id', '=', 'purchase_inventory_items.purchase_inventory_id')
            ->whereBetween('purchase_inventory.created_at', $dateRange)
            ->sum(DB::raw('purchase_inventory_items.qty * purchase_inventory_items.rate'));
    }

    private function inventoryValuation(): array
    {
        $row = InventoryItem::query()
            ->leftJoinSub($this->stockNetQtySubquery(), 'stock', function ($join) {
                $join->on('stock.inventory_item_id', '=', 'inventory_items.id');
            })
            ->selectRaw('COALESCE(SUM(GREATEST(COALESCE(stock.net_qty, 0), 0) * inventory_items.price_per_unit), 0) as value')
            ->selectRaw('COALESCE(SUM(GREATEST(COALESCE(stock.net_qty, 0), 0)), 0) as units')
            ->first();

        return [
            'value' => (float) ($row->value ?? 0),
            'units' => (int) ($row->units ?? 0),
        ];
    }

    private function getBestSellers(array $dateRange, int $limit = 5): array
    {
        return DB::table('sales_products')
            ->join('sales', 'sales.id', '=', 'sales_products.sales_id')
            ->join('inventory_items', 'inventory_items.id', '=', 'sales_products.product_id')
            ->whereBetween('sales.created_at', $dateRange)
            ->select(
                'inventory_items.title',
                DB::raw('SUM(sales_products.qty) as qty'),
                DB::raw('SUM(sales_products.qty * sales_products.price_per_unit) as amount')
            )
            ->groupBy('inventory_items.id', 'inventory_items.title')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->title,
                'qty' => (int) $r->qty,
                'amount' => (float) $r->amount,
            ])
            ->all();
    }

    private function getTopCustomers(array $dateRange, int $limit = 5): array
    {
        return DB::table('sales')
            ->join('customers', 'customers.id', '=', 'sales.customer_id')
            ->join('sales_products', 'sales_products.sales_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', $dateRange)
            ->select(
                'customers.name',
                DB::raw('SUM(sales_products.qty * sales_products.price_per_unit) as spend'),
                DB::raw('COUNT(DISTINCT sales.id) as orders')
            )
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('spend')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name,
                'spend' => (float) $r->spend,
                'orders' => (int) $r->orders,
            ])
            ->all();
    }

    private function getRecentPurchases(array $dateRange, int $limit = 5): array
    {
        return DB::table('purchase_inventory')
            ->leftJoin('purchase_inventory_items', 'purchase_inventory_items.purchase_inventory_id', '=', 'purchase_inventory.id')
            ->whereBetween('purchase_inventory.created_at', $dateRange)
            ->select(
                'purchase_inventory.id',
                'purchase_inventory.vendor',
                'purchase_inventory.bill_date',
                DB::raw('SUM(purchase_inventory_items.qty * purchase_inventory_items.rate) as total')
            )
            ->groupBy('purchase_inventory.id', 'purchase_inventory.vendor', 'purchase_inventory.bill_date')
            ->orderByDesc('purchase_inventory.id')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'vendor' => $r->vendor,
                'bill_date' => $r->bill_date ? Carbon::parse($r->bill_date)->format('Y-m-d') : '',
                'total' => (float) ($r->total ?? 0),
            ])
            ->all();
    }

    public function getInventoryByCategory(): array
    {
        $rows = InventoryItem::query()
            ->leftJoinSub($this->stockNetQtySubquery(), 'stock', function ($join) {
                $join->on('stock.inventory_item_id', '=', 'inventory_items.id');
            })
            ->leftJoin('categories', 'categories.id', '=', 'inventory_items.category_id')
            ->select(
                DB::raw("COALESCE(categories.title, 'Uncategorized') as category"),
                DB::raw('SUM(GREATEST(COALESCE(stock.net_qty, 0), 0) * inventory_items.price_per_unit) as value'),
                DB::raw('SUM(GREATEST(COALESCE(stock.net_qty, 0), 0)) as units')
            )
            ->groupBy('category')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($r) => [
                'category' => $r->category,
                'value' => (float) $r->value,
                'units' => (int) $r->units,
            ])
            ->all();

        return ['categories' => $rows];
    }

    public function getPurchasesByVendor($filterType = null, $fromDate = null, $toDate = null, int $limit = 8): array
    {
        [$from, $to] = $this->resolveDateRange($filterType, $fromDate, $toDate);
        $dateRange = [$from, $to];

        $rows = DB::table('purchase_inventory')
            ->leftJoin('purchase_inventory_items', 'purchase_inventory_items.purchase_inventory_id', '=', 'purchase_inventory.id')
            ->whereBetween('purchase_inventory.created_at', $dateRange)
            ->select('purchase_inventory.vendor', DB::raw('SUM(purchase_inventory_items.qty * purchase_inventory_items.rate) as total'))
            ->groupBy('purchase_inventory.vendor')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'vendor' => $r->vendor,
                'total' => (float) $r->total,
            ])
            ->all();

        return [
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
            'vendors' => $rows,
        ];
    }

    public function getProfitBreakdown($filterType = null, $fromDate = null, $toDate = null): array
    {
        [$from, $to] = $this->resolveDateRange($filterType, $fromDate, $toDate);
        $dateRange = [$from, $to];
        $costMap = $this->itemCostMap();

        $rows = DB::table('sales_products')
            ->join('sales', 'sales.id', '=', 'sales_products.sales_id')
            ->join('inventory_items', 'inventory_items.id', '=', 'sales_products.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'inventory_items.category_id')
            ->whereBetween('sales.created_at', $dateRange)
            ->select(
                DB::raw("COALESCE(categories.title, 'Uncategorized') as category"),
                'sales_products.product_id',
                DB::raw('SUM(sales_products.qty) as qty'),
                DB::raw('SUM(sales_products.qty * sales_products.price_per_unit) as revenue')
            )
            ->groupBy('category', 'sales_products.product_id')
            ->get();

        $byCategory = [];
        foreach ($rows as $row) {
            $cat = $row->category;
            if (!isset($byCategory[$cat])) {
                $byCategory[$cat] = ['category' => $cat, 'revenue' => 0.0, 'cost' => 0.0];
            }
            $byCategory[$cat]['revenue'] += (float) $row->revenue;
            $byCategory[$cat]['cost'] += ($costMap[$row->product_id] ?? 0) * (int) $row->qty;
        }

        $categories = array_map(function ($c) {
            $profit = $c['revenue'] - $c['cost'];
            $c['profit'] = $profit;
            $c['margin'] = $c['revenue'] > 0 ? round($profit / $c['revenue'] * 100, 1) : 0.0;
            return $c;
        }, array_values($byCategory));

        usort($categories, fn ($a, $b) => $b['profit'] <=> $a['profit']);

        $totalRevenue = array_sum(array_column($categories, 'revenue'));
        $totalCost = array_sum(array_column($categories, 'cost'));
        $grossProfit = $totalRevenue - $totalCost;

        return [
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'margin' => $totalRevenue > 0 ? round($grossProfit / $totalRevenue * 100, 1) : 0.0,
            'categories' => $categories,
        ];
    }

    public function getSalesTrend($filterType = null, $fromDate = null, $toDate = null): array
    {
        [$from, $to] = $this->resolveDateRange($filterType, $fromDate, $toDate);
        $dateRange = [$from, $to];
        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay());

        // adaptive granularity: daily for short ranges, monthly for long ones
        $monthly = $days > 62;
        $sqlDate = $monthly ? "DATE_FORMAT(sales.created_at, '%Y-%m-01')" : "DATE(sales.created_at)";

        $gross = DB::table('sales_products')
            ->join('sales', 'sales.id', '=', 'sales_products.sales_id')
            ->whereBetween('sales.created_at', $dateRange)
            ->select(DB::raw("$sqlDate as bucket"), DB::raw('SUM(sales_products.qty * sales_products.price_per_unit) as gross'))
            ->groupBy('bucket')
            ->pluck('gross', 'bucket');

        $discount = DB::table('sales')
            ->whereBetween('created_at', $dateRange)
            ->select(DB::raw(($monthly ? "DATE_FORMAT(created_at, '%Y-%m-01')" : "DATE(created_at)") . ' as bucket'), DB::raw('SUM(discount) as disc'))
            ->groupBy('bucket')
            ->pluck('disc', 'bucket');

        $points = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $monthly ? $cursor->format('Y-m-01') : $cursor->format('Y-m-d');
            $g = (float) ($gross[$key] ?? 0);
            $d = (float) ($discount[$key] ?? 0);
            $points[$key] = [
                'date' => $key,
                'label' => $monthly ? $cursor->format('M Y') : $cursor->format('M j'),
                'revenue' => max($g - $d, 0),
            ];
            $monthly ? $cursor->addMonthNoOverflow()->startOfMonth() : $cursor->addDay();
        }

        return [
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
            'granularity' => $monthly ? 'monthly' : 'daily',
            'points' => array_values($points),
        ];
    }

    public function getCategoryBreakdown($filterType = null, $fromDate = null, $toDate = null): array
    {
        [$from, $to] = $this->resolveDateRange($filterType, $fromDate, $toDate);
        $dateRange = [$from, $to];

        $rows = DB::table('sales_products')
            ->join('sales', 'sales.id', '=', 'sales_products.sales_id')
            ->join('inventory_items', 'inventory_items.id', '=', 'sales_products.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'inventory_items.category_id')
            ->whereBetween('sales.created_at', $dateRange)
            ->select(
                DB::raw("COALESCE(categories.title, 'Uncategorized') as category"),
                DB::raw('SUM(sales_products.qty * sales_products.price_per_unit) as revenue'),
                DB::raw('SUM(sales_products.qty) as qty')
            )
            ->groupBy('category')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($row) {
                return [
                    'category' => $row->category,
                    'revenue' => (float) $row->revenue,
                    'qty' => (int) $row->qty,
                ];
            })
            ->all();

        return [
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
            'categories' => $rows,
            'total_revenue' => array_sum(array_column($rows, 'revenue')),
        ];
    }

    public function getPaymentMethodRevenue($filterType = null, $fromDate = null, $toDate = null)
    {
        [$from, $to] = $this->resolveDateRange($filterType, $fromDate, $toDate);
        $dateRange = [$from, $to];

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
            'from_date' => $dateRange[0]->format('Y-m-d'),
            'to_date' => $dateRange[1]->format('Y-m-d'),
            'payment_modes'  => $data,
            'total_revenue'  => $totalRevenue,
            'total_discount' => $totalDiscount,
            'net_revenue'    => $netRevenue,
        ];
    }

    public function getSalesSummary($filterType = null, $fromDate = null, $toDate = null): array
    {
        [$from, $to] = $this->resolveDateRange($filterType, $fromDate, $toDate);
        $dateRange = [$from, $to];
        $stats = $this->computePeriodStats($dateRange);
        $invoiceCount = (int) Sales::whereBetween('created_at', $dateRange)->count();

        $topProducts = DB::table('sales_products')
            ->join('sales', 'sales.id', '=', 'sales_products.sales_id')
            ->join('inventory_items', 'inventory_items.id', '=', 'sales_products.product_id')
            ->whereBetween('sales.created_at', $dateRange)
            ->select(
                'inventory_items.title',
                DB::raw('SUM(sales_products.qty) as total_qty'),
                DB::raw('SUM(sales_products.qty * sales_products.price_per_unit) as total_amount')
            )
            ->groupBy('inventory_items.id', 'inventory_items.title')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'name' => $row->title,
                    'qty' => (int) $row->total_qty,
                    'amount' => (float) $row->total_amount,
                ];
            })
            ->all();

        return [
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
            'total_qty' => $stats['salesQty'],
            'invoice_count' => $invoiceCount,
            'avg_qty_per_invoice' => $invoiceCount > 0
                ? round($stats['salesQty'] / $invoiceCount, 1)
                : 0,
            'total_revenue' => $stats['revenue'],
            'top_products' => $topProducts,
        ];
    }

    private function resolveDateRange($filterType = null, $fromDate = null, $toDate = null): array
    {
        if ($fromDate && $toDate) {
            return [
                Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay(),
            ];
        }

        return $this->getDateRange($filterType ?? 'Monthly');
    }

    private function computePeriodStats(array $dateRange): array
    {
        $salesIds = Sales::whereBetween('created_at', $dateRange)->pluck('id');
        $revenue = 0;
        $salesQty = 0;

        if ($salesIds->isNotEmpty()) {
            foreach ($salesIds as $saleId) {
                $itemsTotal = SalesProduct::where('sales_id', $saleId)
                    ->sum(DB::raw('price_per_unit * qty'));
                $discount = Sales::where('id', $saleId)->value('discount') ?? 0;
                $revenue += max($itemsTotal - $discount, 0);
            }
            $salesQty = (int) SalesProduct::whereIn('sales_id', $salesIds)->sum('qty');
        }

        return [
            'revenue' => $revenue,
            'salesQty' => $salesQty,
        ];
    }

    private function getRecentSales(array $dateRange, int $limit = 5): array
    {
        $sales = DB::table('sales')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->select(
                'sales.id',
                'sales.created_at',
                'sales.discount',
                'customers.name as customer_title'
            )
            ->whereBetween('sales.created_at', $dateRange)
            ->orderByDesc('sales.id')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($sales as $sale) {
            $itemsTotal = (float) SalesProduct::where('sales_id', $sale->id)
                ->sum(DB::raw('price_per_unit * qty'));
            $qty = (int) SalesProduct::where('sales_id', $sale->id)->sum('qty');
            $discount = (float) ($sale->discount ?? 0);
            $amount = max($itemsTotal - $discount, 0);

            $payments = DB::table('sales_payment_mode')
                ->join('payment_modes', 'payment_modes.id', '=', 'sales_payment_mode.payment_mode_id')
                ->where('sales_payment_mode.sales_id', $sale->id)
                ->pluck('payment_modes.payment_title')
                ->unique()
                ->values()
                ->all();

            if (empty($payments)) {
                $modeIds = SalesProduct::where('sales_id', $sale->id)->value('payment_mode');
                if ($modeIds) {
                    $ids = array_filter(array_map('trim', explode(',', (string) $modeIds)));
                    if (!empty($ids)) {
                        $payments = DB::table('payment_modes')
                            ->whereIn('id', $ids)
                            ->pluck('payment_title')
                            ->all();
                    }
                }
            }

            $createdAt = Carbon::parse($sale->created_at);
            $result[] = [
                'id' => $sale->id,
                'invoice_no' => 'INV-' . $createdAt->format('Y') . '-' . str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT),
                'datetime' => $createdAt->format('Y-m-d H:i'),
                'customer' => $sale->customer_title ?: 'Walk-in',
                'payment_methods' => $payments ?: ['Cash'],
                'qty' => $qty,
                'amount' => $amount,
                'status' => 'Paid',
            ];
        }

        return $result;
    }

    private function getTopLowStockItems(int $limit = 5): array
    {
        return $this->getLowStockItems($limit);
    }

    public function getLowStockItems(?int $limit = null): array
    {
        $stockSub = $this->stockNetQtySubquery();

        $query = InventoryItem::query()
            ->leftJoinSub($stockSub, 'stock', function ($join) {
                $join->on('stock.inventory_item_id', '=', 'inventory_items.id');
            })
            ->select(
                'inventory_items.id',
                'inventory_items.title',
                DB::raw('COALESCE(stock.net_qty, 0) as net_qty')
            )
            ->whereRaw('COALESCE(stock.net_qty, 0) <= ?', [self::LOW_STOCK_THRESHOLD])
            ->orderByRaw('COALESCE(stock.net_qty, 0) ASC')
            ->orderBy('inventory_items.title');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($row) {
            $qty = (int) $row->net_qty;

            return [
                'id' => $row->id,
                'name' => $row->title,
                'in_stock' => max(0, $qty),
                'status' => $qty <= 0 ? 'Out of Stock' : 'Low',
            ];
        })->all();
    }

    private function stockNetQtySubquery()
    {
        return DB::table('inventory_stocks')
            ->select(
                'inventory_item_id',
                DB::raw('SUM(CASE WHEN purchase_inventory_id IS NOT NULL THEN qty ELSE 0 END) - SUM(CASE WHEN sales_id IS NOT NULL THEN qty ELSE 0 END) as net_qty')
            )
            ->groupBy('inventory_item_id');
    }

    private function outOfStockCount(): int
    {
        return (int) InventoryItem::query()
            ->leftJoinSub($this->stockNetQtySubquery(), 'stock', function ($join) {
                $join->on('stock.inventory_item_id', '=', 'inventory_items.id');
            })
            ->whereRaw('COALESCE(stock.net_qty, 0) <= 0')
            ->count();
    }

    private function topVendorForRange(array $dateRange): array
    {
        $row = DB::table('purchase_inventory')
            ->leftJoin('purchase_inventory_items', 'purchase_inventory_items.purchase_inventory_id', '=', 'purchase_inventory.id')
            ->whereBetween('purchase_inventory.created_at', $dateRange)
            ->select('purchase_inventory.vendor', DB::raw('SUM(purchase_inventory_items.qty * purchase_inventory_items.rate) as total'))
            ->groupBy('purchase_inventory.vendor')
            ->orderByDesc('total')
            ->first();

        return [
            'name' => $row->vendor ?? '—',
            'total' => (float) ($row->total ?? 0),
        ];
    }

    private function getStockAlertCount(): int
    {
        return (int) InventoryItem::query()
            ->leftJoinSub($this->stockNetQtySubquery(), 'stock', function ($join) {
                $join->on('stock.inventory_item_id', '=', 'inventory_items.id');
            })
            ->whereRaw('COALESCE(stock.net_qty, 0) <= ?', [self::LOW_STOCK_THRESHOLD])
            ->count();
    }

    private function percentChange($current, $previous): float
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
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
