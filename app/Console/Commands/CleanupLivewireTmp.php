<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupLivewireTmp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:livewire-tmp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old Livewire temporary files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = Storage::files('temp');        
        foreach ($files as $file) {
            $lastModified = Storage::lastModified($file);
            if ($lastModified < now()->subMinutes(60)->getTimestamp()) {
                Storage::delete($file);   
            }       
        }
        return 0;
    }
}
