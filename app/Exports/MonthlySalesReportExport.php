<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonthlySalesReportExport implements FromView, WithTitle, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function __construct($data, $monthDays, $rowData)
    {
        $this->data = $data;
        $this->monthDays = $monthDays;
        $this->rowData = $rowData;
    }
    public function view(): view
    {

        ini_set('memory_limit', '-1');

        return view('report.monthly_sales_report', [
            'monthDays' => $this->monthDays,
            'rowData' => $this->rowData,
        ]);
    }

    public function title(): string
    {
        $department = 'Sales Report';
        return ucfirst($department);
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {},
        ];
    }
}
