<?php

namespace App\Listeners;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Makes the /up health check answer 500 when the database or the cache
 * store cannot be reached, so load balancers and uptime monitors notice.
 * Registered through event discovery. The framework logs the failure and
 * never shows its details in the response.
 */
class CheckApplicationHealth
{
    public function handle(DiagnosingHealth $event): void
    {
        DB::select('select 1');

        Cache::get('health-check');
    }
}
