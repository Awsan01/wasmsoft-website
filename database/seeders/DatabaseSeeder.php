<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Production installs create their first administrator with
     * `php artisan wasmsoft:create-admin`; demo accounts and demonstration
     * content exist only in local development.
     */
    public function run(): void
    {
        if (app()->environment('local', 'testing')) {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
