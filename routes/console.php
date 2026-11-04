<?php

use Illuminate\Support\Facades\Schedule;

// One short-lived worker; never retry historical failures automatically.
if (config('operations.run_queue_from_scheduler')) {
    Schedule::command('queue:work --stop-when-empty --max-time=45 --max-jobs=10 --timeout=30 --tries=3 --memory=128')
        ->everyMinute()
        ->withoutOverlapping(5);
}
