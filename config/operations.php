<?php

return [
    // Enable only when cron is the sole queue runner (no Supervisor worker).
    'run_queue_from_scheduler' => env('QUEUE_CRON_ENABLED', false),
];
