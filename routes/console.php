<?php

use App\Console\Commands\CleanupLivewireTmp;
use Illuminate\Support\Facades\Schedule;


Schedule::command(CleanupLivewireTmp::class)->everyFiveSeconds();