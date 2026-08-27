<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReportExport;
use App\Exports\MonthlySalesReportExport;
use App\Exports\InventoryReportExport;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Repository\ReportRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private $reportRepo;
    public function __construct(ReportRepository $reportRepo)
    {
        $this->reportRepo = $reportRepo;
    }

    public function index()
    {
        $data['months'] = $this->reportRepo->getNepaliMonths();
        $data['years'] = $this->reportRepo->getNepaliYears();
        return view('report.index')->with('data', $data);
    }

    public function salesReports()
    {
        $data['months'] = $this->reportRepo->getNepaliMonths();
        $data['years'] = $this->reportRepo->getNepaliYears();
        return view('report.sales_report')->with('data', $data);
    }

    public function getReport(Request $request)
    {
        $monthDays = $this->reportRepo->getMonthAllDays($request->all());
        $rowData = $this->reportRepo->getExportData($request->all());

        $name = "purchase_inventory_" . $request->all()['year'] . "_" . $request->all()['month'] . ".xlsx";

        return Excel::download(new MonthlyReportExport($request->all(), $monthDays, $rowData), $name);
        $data = $this->reportRepo->getMothlyReport($request->all());
    }

    public function getSalesReport(Request $request)
    {
        $monthDays = $this->reportRepo->getMonthAllDays($request->all());
        $rowData = $this->reportRepo->getSalesExportData($request->all());

        $name = "sales_" . $request->all()['year'] . "_" . $request->all()['month'] . ".xlsx";

        return Excel::download(new MonthlySalesReportExport($request->all(), $monthDays, $rowData), $name);
        $data = $this->reportRepo->getMothlyReport($request->all());
    }

    public function inventoryReport()
    {
        $categories = Category::orderBy('title', 'asc')->get();
        return view('report.inventory_report')->with('categories', $categories);
    }

    public function getInventoryReport(Request $request)
    {
        $categoryId = $request->category_id;
        $category = Category::find($categoryId);

        if (!$category) {
            return redirect()->back()->with(['message' => 'Category not found!', 'type' => 'error']);
        }

        // Get inventory items by category, sorted alphabetically by title
        $inventoryItems = InventoryItem::where('category_id', $categoryId)
            ->orderBy('title', 'asc')
            ->select('id', 'title', 'unit', 'price_per_unit')
            ->get();

        $name = $category->title . "_price_list.xlsx";

        return Excel::download(new InventoryReportExport($categoryId, $category->title, $inventoryItems), $name);
    }
}
