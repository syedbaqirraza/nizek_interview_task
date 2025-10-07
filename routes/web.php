<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\StockUpload;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/upload', StockUpload::class)->name('stock.upload');


Route::get('/cleanup-temp', function () {
    $files = Storage::files('temp');
    
    $deleted = 0;
    $errors = [];
    
    foreach ($files as $file) {
        try {
            Storage::delete($file);
            $deleted++;
        } catch (\Exception $e) {
            $errors[] = $file . ': ' . $e->getMessage();
        }
    }
    
    return [
        'total_files' => count($files),
        'deleted' => $deleted,
        'errors' => $errors
    ];
});