<?php

namespace Tests\Feature\Console\Commands;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdministratorTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_an_active_administrator_with_the_typed_password(): void
    {
        $this->artisan('wasmsoft:create-admin', ['--name' => 'Site Owner', '--email' => 'Owner@Example.com'])
            ->expectsQuestion('Password', 'Strong-Password-99')
            ->expectsQuestion('Confirm password', 'Strong-Password-99')
            ->assertSuccessful();

        $user = User::query()->where('email', 'owner@example.com')->sole();
        $this->assertSame(Role::Administrator, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('Strong-Password-99', $user->password));
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::UserCreated->value, 'subject_id' => $user->id]);
    }

    public function test_refuses_an_email_that_is_already_registered(): void
    {
        User::factory()->create(['email' => 'owner@example.com']);

        $this->artisan('wasmsoft:create-admin', ['--name' => 'Site Owner', '--email' => 'owner@example.com'])
            ->assertFailed();

        $this->assertSame(1, User::query()->count());
    }
}
