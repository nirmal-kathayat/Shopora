<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;

class InventoryReportExport implements FromView, WithTitle, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function __construct($categoryId, $categoryName, $inventoryItems)
    {
        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
        $this->inventoryItems = $inventoryItems;
    }

    public function view(): view
    {
        ini_set('memory_limit', '-1');

        return view('report.inventory_report_export', [
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'inventoryItems' => $this->inventoryItems,
        ]);
    }

    public function title(): string
    {
        return 'Inventory Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {},
        ];
    }
}
