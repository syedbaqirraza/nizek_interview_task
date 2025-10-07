<?php

namespace App\Repositories;

use App\Models\StockPrice;
use Carbon\Carbon;

class StockRepository
{
    public function getLatestPrice(int $companyId): ?StockPrice
    {
        return StockPrice::where('company_id', $companyId)
            ->orderBy('date', 'desc')
            ->first();
    }

    public function getOldestPrice(int $companyId): ?StockPrice
    {
        return StockPrice::where('company_id', $companyId)
            ->orderBy('date', 'asc')
            ->first();
    }

    public function getPriceByDate(int $companyId, string $date): ?StockPrice
    {
        // Try exact date first
        $price = StockPrice::where('company_id', $companyId)
            ->where('date', $date)
            ->first();

        // If not found, get closest previous date
        if (!$price) {
            $price = StockPrice::where('company_id', $companyId)
                ->where('date', '<=', $date)
                ->orderBy('date', 'desc')
                ->first();
        }

        return $price;
    }

    public function getPricesBetweenDates(int $companyId, string $startDate, string $endDate)
    {
        return StockPrice::where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();
    }
}