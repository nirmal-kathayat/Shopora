<?php

namespace App\Http\Controllers;

use App\Repository\DashboardRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $repo;

    public function __construct(DashboardRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        try {
            $filterType = $request->query('type', 'Monthly');
            // dd($filterType);
            $data = $this->repo->getFilteredData($filterType);
            $paymentModes = \DB::table('payment_modes')->get();

            return view('dashboard.index', [
                'data' => $data,
                'paymentModes' => $paymentModes
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function getFilteredStats(Request $request)
    {
        try {
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');
            $stats = $this->repo->getFilteredData(null, $fromDate, $toDate);
            return response()->json($stats);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'error']);
        }
    }

    public function getPaymentMethodRevenue(Request $request)
    {
        try {
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');
            $data = $this->repo->getPaymentMethodRevenue(null, $fromDate, $toDate);
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
