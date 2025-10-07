<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StockPriceImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessStockFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePath;
    public $companyId;

    public function __construct($filePath, $companyId)
    {
        $this->filePath = $filePath;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        Excel::import(
            new StockPriceImport($this->companyId), 
            $this->filePath
        );
    }

}