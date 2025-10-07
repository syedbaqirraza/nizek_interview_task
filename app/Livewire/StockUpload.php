<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Company;
use App\Jobs\ProcessStockFile;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StockUpload extends Component
{
    use WithFileUploads;

    public $file;
    public $company_id;
    public $message;

    public function updatedFile()
    {
        Log::info('File updated:', [
            'file' => $this->file ? $this->file->getClientOriginalName() : 'No file'
        ]);
    }

    public function hideMessage()
    {
        if ($this->message) {
            $this->message = '';
        }
    }

    public function submitUpload()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:51200',
            'company_id' => 'required|exists:companies,id'
        ],[
            'file.required' => 'Please select a file to upload.',
            'file.mimes' => 'Only Excel or CSV files are allowed.',
            'file.max' => 'File size must not exceed 50MB.',
            'company_id.required' => 'Please select a company.',
        ]);

        $path = $this->file->store('temp', 'local');

        // Check file exists
        if (!Storage::exists($path)) {
            Log::error('File not found after storage');
            $this->message = 'Error: File not saved properly';
            return;
        }

        // Dispatch job
        ProcessStockFile::dispatch($path, $this->company_id);

        $this->message = 'File is being processed in background!';
        
        $this->reset(['file', 'company_id']);
    }

    public function render()
    {
        $companies = Company::all();
        
        return view('livewire.stock-upload', [
            'companies' => $companies
        ]);
    }
}