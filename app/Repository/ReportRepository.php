<?php

namespace App\Repository;

use DB;
use Carbon\Carbon;

class ReportRepository
{
    public function __construct() {}

    public function getNepaliMonths()
    {
        $data = [
            '1' => "Baishak",
            '2' => "Jestha",
            '3' => "Ashad",
            '4' => "Shrawn",
            '5' => "Bhadra",
            '6' => "Ashwin",
            '7' => "kartik",
            '8' => "Mangshir",
            '9' => "Poush",
            '10' => "Magh",
            '11' => "Falgun",
            '12' => "Chaitra",
        ];
        return $data;
    }

    public function getNepaliYears()
    {
        $data = [
            '81' => '2081',
            '82' => '2082',
            '83' => '2083',
            '84' => '2084',
            '85' => '2085',
            '86' => '2086',
            '87' => '2087',
            '88' => '2088',
            '89' => '2089',
            '90' => '2090'
        ];
        return $data;
    }
    public function getDaysNumberofMonth($result)
    {
        $data = [
            81 => array(2081, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31),
            82 => array(2082, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30),
            83 => array(2083, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30),
            84 => array(2084, 31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30),
            85 => array(2085, 31, 32, 31, 32, 30, 31, 30, 30, 29, 30, 30, 30),
            86 => array(2086, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30),
            87 => array(2087, 31, 31, 32, 31, 31, 31, 30, 30, 29, 30, 30, 30),
            88 => array(2088, 30, 31, 32, 32, 30, 31, 30, 30, 29, 30, 30, 30),
            89 => array(2089, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30),
            90 => array(2090, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30)
        ];
        $days = $data[$result['year']][$result['month']];
        return $days;
    }

    public function getMonthAllDays($data)
    {
        $days = $this->getDaysNumberofMonth($data);
        $months = $this->getNepaliMonths();
        $name = $months[$data['month']];
        $days = collect(range(1, $days))->map(function ($day) use ($name) {
            return $name . "-" . $day;
        })->toArray();
        return $days;
    }

    public function getExportData($data)
    {
        $month  = ($data['month'] < 10) ? str_pad($data['month'], 2, '0', STR_PAD_LEFT) : $data['month'];
        $year = $this->getNepaliYears();
        $year = $year[$data['year']];
        $startDate = $year . "-" . $month . "-01";
        $noOfDays = $this->getDaysNumberofMonth($data);
        $endDate = $year . "-" . $month . "-" . $noOfDays;

        $datas = DB::table('purchase_inventory')
            ->leftJoin('inventory_items', 'purchase_inventory.inventory_item_id', '=', 'inventory_items.id')
            ->whereBetween('purchase_inventory.purchase_date', [$startDate, $endDate])
            ->select('purchase_inventory.*', 'inventory_items.title', 'purchase_inventory.per_piece_rate')
            ->get();

        $totalSum = $datas->sum(function ($item) {
            return $item->per_piece_rate * $item->qty;
        });

        $groupedByTitle = $datas->groupBy('title');

        $result = [];

        foreach ($groupedByTitle as $title => $group) {
            $groupedByRate = $group->groupBy('per_piece_rate');
            $rateGroups = [];

            foreach ($groupedByRate as $rate => $rateGroup) {
                $rateGroups[] = [
                    'rate' => $rate,
                    'items' => $rateGroup->toArray(),
                ];
            }

            $result[] = [
                'title' => $title,
                'inventory_item_id' => $group[0]->inventory_item_id,
                'rate_groups' => $rateGroups,
            ];
        }
        $preparedData = [];

        foreach ($result as $key => $value) {
            foreach ($value['rate_groups'] as $key1 => $value1) {
                $data = [];
                $data['title'] = $value['title'];
                $data['rate'] = $value1['rate'];
                $totalQty = 0;

                foreach ($value1['items'] as $key2 => $value2) {
                    $totalQty += $value2->qty;
                }

                $data['monthQtyData'] = DB::table('purchase_inventory')
                    ->leftJoin('inventory_items', 'purchase_inventory.inventory_item_id', '=', 'inventory_items.id')
                    ->whereBetween('purchase_inventory.purchase_date', [$startDate, $endDate])
                    ->where('inventory_item_id', $value['inventory_item_id'])
                    ->where('per_piece_rate', $value1['rate'])
                    ->select('qty', 'purchase_date')
                    ->get()
                    ->groupBy('purchase_date')
                    ->map(function ($group) {
                        return [
                            'purchase_date' => $group->first()->purchase_date,
                            'total_qty' => $group->sum('qty'),
                        ];
                    })
                    ->values()
                    ->toArray();

                $data['qty'] = $totalQty;
                $preparedData[] = $data;
            }
        }

        foreach ($preparedData as $key => $value) {
            $qtyDataInside = array_fill(0, $noOfDays, 0);

            foreach ($value['monthQtyData'] as $key1 => $value2) {
                $parsedDate = Carbon::parse($value2["purchase_date"]);

                for ($i = 0; $i < $noOfDays; $i++) {
                    if ($parsedDate->day === $i + 1) {
                        $qtyDataInside[$i] = $value2['total_qty'];
                    }
                }
            }

            $preparedData[$key]['insideData'] = $qtyDataInside;
        }

        $toReturn['preparedData'] = $preparedData;
        $toReturn['totalSum'] = $totalSum;
        return $toReturn;
    }

    public function getSalesExportData($data)
    {
        $month  = ($data['month'] < 10) ? str_pad($data['month'], 2, '0', STR_PAD_LEFT) : $data['month'];
        $year = $this->getNepaliYears();
        $year = $year[$data['year']];
        $startDate = $year . "-" . $month . "-01";
        $noOfDays = $this->getDaysNumberofMonth($data);
        $endDate = $year . "-" . $month . "-" . $noOfDays;

        $datas = DB::table('sales')
            ->leftJoin('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->leftJoin('inventory_items', 'inventory_items.id', '=', 'sales_products.product_id')
            ->whereBetween('sales.nepali_date', [$startDate, $endDate])
            ->select('sales.*', 'sales_products.*', 'inventory_items.title')
            ->get();

        $totalSum = $datas->sum(function ($item) {
            return $item->price_per_unit * $item->qty;
        });

        $groupedByTitle = $datas->groupBy('title');
        $result = [];

        foreach ($groupedByTitle as $title => $group) {
            $groupedByRate = $group->groupBy('price_per_unit');
            $rateGroups = [];
            foreach ($groupedByRate as $rate => $rateGroup) {
                $rateGroups[] = [
                    'rate' => $rate,
                    'items' => $rateGroup->toArray(),
                ];
            }
            $result[] = [
                'title' => $title,
                'inventory_item_id' => $group[0]->product_id,
                'rate_groups' => $rateGroups,
            ];
        }

        $preparedData = [];

        foreach ($result as $key => $value) {
            foreach ($value['rate_groups'] as $key1 => $value1) {
                $data = [];
                $data['title'] = $value['title'];
                $data['rate'] = $value1['rate'];
                $totalQty = 0;
                foreach ($value1['items'] as $key2 => $value2) {
                    $totalQty += $value2->qty;
                }

                $data['monthQtyData'] = DB::table('sales')
                    ->leftJoin('sales_products', 'sales.id', '=', 'sales_products.sales_id')
                    ->whereBetween('sales.nepali_date', [$startDate, $endDate])
                    ->where('sales_products.product_id', $value['inventory_item_id'])
                    ->where('price_per_unit', $value1['rate'])
                    ->select('qty', 'nepali_date')
                    ->get()
                    ->groupBy('nepali_date')
                    ->map(function ($group) {
                        return [
                            'nepali_date' => $group->first()->nepali_date,
                            'total_qty' => $group->sum('qty'),
                        ];
                    })
                    ->values()
                    ->toArray();
                $data['qty'] = $totalQty;
                $preparedData[] = $data;
            }
        }
        foreach ($preparedData as $key => $value) {
            $qtyDataInside = array_fill(0, $noOfDays, 0);

            foreach ($value['monthQtyData'] as $key1 => $value2) {
                $parsedDate = Carbon::parse($value2["nepali_date"]);

                for ($i = 0; $i < $noOfDays; $i++) {
                    if ($parsedDate->day === $i + 1) {
                        $qtyDataInside[$i] = $value2['total_qty'];
                    }
                }
            }

            $preparedData[$key]['insideData'] = $qtyDataInside;
        }

        $toReturn['preparedData'] = $preparedData;
        $toReturn['totalSum'] = $totalSum;
        return $toReturn;
    }

    public function getMothlyReport($data)
    {
        dd($data);
    }
}
