<?php

/*
|--------------------------------------------------------------------------
| Scheduled tasks (the server's cron runs `php artisan schedule:run` every minute)
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Schedule;

Schedule::command('wasmsoft:release-scheduled')->everyMinute()->withoutOverlapping();
