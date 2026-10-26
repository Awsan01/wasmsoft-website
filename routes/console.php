<?php

/*
|--------------------------------------------------------------------------
| Scheduled tasks (the server's cron runs `php artisan schedule:run` every minute)
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Schedule;

Schedule::command('wasmsoft:release-scheduled')->everyMinute()->withoutOverlapping();

// Retention: deletes inquiries and audit entries older than their configured
// periods (config/wasmsoft.php, models with the Prunable trait).
Schedule::command('model:prune')->daily()->at('03:15');
