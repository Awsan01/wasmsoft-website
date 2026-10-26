<?php

namespace Database\Seeders;

use App\Auth\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Local development data: one demo account per role, then clearly marked
 * placeholder content (DemoContentSeeder).
 *
 * All accounts share the password in DEV_SEED_PASSWORD (see .env.example).
 * Refuses to run anywhere but local and testing environments.
 */
class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Development seed data must never be loaded outside local environments.');
        }

        $password = (string) config('wasmsoft.development.seed_password');

        foreach (Role::cases() as $role) {
            $user = User::query()->firstOrNew(['email' => "{$role->value}@wasmsoft.test"]);
            $user->fill([
                'name' => 'Demo '.ucfirst($role->value),
                'password' => $password,
            ]);
            $user->role = $role;
            $user->is_active = true;
            $user->email_verified_at = now();
            $user->save();
        }

        $this->call(DemoContentSeeder::class);
    }
}
