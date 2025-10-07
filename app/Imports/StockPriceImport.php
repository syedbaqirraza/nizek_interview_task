<?php

namespace App\Imports;

use App\Models\StockPrice;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StockPriceImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function model(array $row)
    {
        if (empty($row['date']) || empty($row['stock_price'])) {
            return null; // skip this row
        }
        // Normalize date: Excel serial number OR string date

        if (is_numeric($row['date'])) {
            // Excel serial date
            $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date']);
            $date = Carbon::instance($dt)->format('Y-m-d');
        } else {
            // Try parsing common string formats
            $date = Carbon::parse($row['date'])->format('Y-m-d');
        }
       

        if (StockPrice::where('company_id', $this->companyId)->where('date', $date)->exists()) {
            return null; // skip duplicate
        }

        return new StockPrice([
            'company_id' => $this->companyId,
            'date' => $date,
            'stock_price' => $row['stock_price']
        ]);
        
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}