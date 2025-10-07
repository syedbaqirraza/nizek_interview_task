<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\StockCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(StockCalculationService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function getPeriodChanges(Company $company, Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'required|in:1D,1M,3M,6M,YTD,1Y,3Y,5Y,10Y,MAX'
        ]);

        $period = $request->input('period');
        $result = $this->stockService->calculatePeriodChange($company->id, $period);

        if (!$result) {
            return response()->json([
                'error' => 'Insufficient data for the requested period'
            ], 404);
        }

        return response()->json($result);
    }

    public function getCustomDateComparison(Company $company, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $result = $this->stockService->calculateCustomDateChange(
            $company->id, 
            $startDate, 
            $endDate
        );

        if (!$result) {
            return response()->json([
                'error' => 'No data found for the specified date range'
            ], 404);
        }

        return response()->json($result);
    }
}