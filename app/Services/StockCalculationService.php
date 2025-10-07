<?php

namespace App\Services;

use App\Repositories\StockRepository;
use Carbon\Carbon;

class StockCalculationService
{
    protected $stockRepository;

    public function __construct(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    public function calculatePeriodChange(int $companyId, string $period): ?array
    {
        $latestPrice = $this->stockRepository->getLatestPrice($companyId);
        
        if (!$latestPrice) {
            return null;
        }

        $targetDate = $this->getTargetDateForPeriod($latestPrice->date, $period, $companyId);
        
        if (!$targetDate) {
            return null;
        }

        $oldPrice = $this->stockRepository->getPriceByDate($companyId, $targetDate);

        if (!$oldPrice) {
            return null;
        }

        return $this->formatResponse(
            $period,
            $latestPrice->stock_price,
            $oldPrice->stock_price,
            $latestPrice->date,
            $oldPrice->date
        );
    }

    public function calculateCustomDateChange(int $companyId, string $startDate, string $endDate): ?array
    {
        $startPrice = $this->stockRepository->getPriceByDate($companyId, $startDate);
        $endPrice = $this->stockRepository->getPriceByDate($companyId, $endDate);

        if (!$startPrice || !$endPrice) {
            return null;
        }

        return [
            'start_date' => $startPrice->date->format('Y-m-d'),
            'end_date' => $endPrice->date->format('Y-m-d'),
            'start_price' => (float) $startPrice->stock_price,
            'end_price' => (float) $endPrice->stock_price,
            'value_change' => round($endPrice->stock_price - $startPrice->stock_price, 2),
            'percentage_change' => $this->calculatePercentage($startPrice->stock_price, $endPrice->stock_price)
        ];
    }

    protected function getTargetDateForPeriod(Carbon $latestDate, string $period, int $companyId): ?string
    {
        switch ($period) {
            case '1D':
                return $latestDate->copy()->subDay()->format('Y-m-d');
            case '1M':
                return $latestDate->copy()->subMonth()->format('Y-m-d');
            case '3M':
                return $latestDate->copy()->subMonths(3)->format('Y-m-d');
            case '6M':
                return $latestDate->copy()->subMonths(6)->format('Y-m-d');
            case 'YTD':
                return $latestDate->copy()->startOfYear()->format('Y-m-d');
            case '1Y':
                return $latestDate->copy()->subYear()->format('Y-m-d');
            case '3Y':
                return $latestDate->copy()->subYears(3)->format('Y-m-d');
            case '5Y':
                return $latestDate->copy()->subYears(5)->format('Y-m-d');
            case '10Y':
                return $latestDate->copy()->subYears(10)->format('Y-m-d');
            case 'MAX':
                $oldest = $this->stockRepository->getOldestPrice($companyId);
                return $oldest ? $oldest->date->format('Y-m-d') : null;
            default:
                return null;
        }
    }

    protected function calculatePercentage(float $oldPrice, float $newPrice): string
    {
        if ($oldPrice == 0.0) {
            return 'N/A';
        }

        $change = (($newPrice / $oldPrice) - 1) * 100;
        return round($change, 2) . '%';
    }

    protected function formatResponse(string $period, float $currentPrice, float $oldPrice, Carbon $currentDate, Carbon $oldDate): array
    {
        return [
            'period' => $period,
            'current_date' => $currentDate->format('Y-m-d'),
            'old_date' => $oldDate->format('Y-m-d'),
            'current_price' => (float) $currentPrice,
            'old_price' => (float) $oldPrice,
            'value_change' => round($currentPrice - $oldPrice, 2),
            'percentage_change' => $this->calculatePercentage($oldPrice, $currentPrice)
        ];
    }
}