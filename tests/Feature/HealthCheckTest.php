<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_the_application_reports_up_while_its_database_answers(): void
    {
        $this->get('/up')->assertOk();
        $this->getJson('/up')->assertOk()->assertExactJson(['status' => 'up']);
    }

    public function test_an_unreachable_database_fails_the_check_and_is_logged_without_revealing_details(): void
    {
        Exceptions::fake();
        config([
            'app.debug' => false,
            'database.connections.sqlite.database' => storage_path('framework/testing/missing-database.sqlite'),
        ]);
        DB::purge('sqlite');

        $this->get('/up')
            ->assertInternalServerError()
            ->assertDontSee('missing-database');

        $this->getJson('/up')->assertInternalServerError()->assertExactJson(['status' => 'down']);

        Exceptions::assertReported(QueryException::class);
    }
}
