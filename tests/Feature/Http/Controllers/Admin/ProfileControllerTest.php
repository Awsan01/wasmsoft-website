<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_every_role_can_open_its_profile(): void
    {
        $this->actingAs(User::factory()->role(Role::Viewer)->create(['name' => 'Vera Viewer']))->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/profile/edit')
                ->where('form.name', 'Vera Viewer')
                ->where('account.role', Role::Viewer->label())
            );
    }

    public function test_the_name_and_language_can_be_changed_but_not_the_email_or_role(): void
    {
        $user = User::factory()->role(Role::Viewer)->create(['email' => 'vera@wasmsoft.example']);

        $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => 'Vera Yılmaz',
            'locale' => 'tr',
            'email' => 'other@example.com',
            'role' => 'administrator',
        ])->assertRedirect(route('admin.profile.edit'));

        $user->refresh();
        $this->assertSame('Vera Yılmaz', $user->name);
        $this->assertSame('tr', $user->locale);
        $this->assertSame('vera@wasmsoft.example', $user->email);
        $this->assertSame(Role::Viewer, $user->role);
    }

    public function test_changing_the_password_needs_the_current_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('admin.profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'Correct-Horse-42',
            'password_confirmation' => 'Correct-Horse-42',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()?->password ?? ''));
    }

    public function test_the_new_password_is_saved_audited_and_signs_out_other_devices(): void
    {
        $user = User::factory()->create();
        $rememberToken = $user->remember_token;

        $this->actingAs($user)->put(route('admin.profile.password'), [
            'current_password' => 'password',
            'password' => 'Correct-Horse-42',
            'password_confirmation' => 'Correct-Horse-42',
        ])->assertRedirect(route('admin.profile.edit'))->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('Correct-Horse-42', $user->password));
        $this->assertNotSame($rememberToken, $user->remember_token);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::PasswordChanged->value, 'subject_id' => $user->id]);
    }

    public function test_weak_passwords_are_refused(): void
    {
        $this->actingAs(User::factory()->create())->put(route('admin.profile.password'), [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }
}
