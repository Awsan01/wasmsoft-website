<?php

namespace App\Console\Commands;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates the first dashboard administrator (or a replacement when every
 * administrator is locked out). The password is always typed at a prompt,
 * never passed as an option, so it does not end up in shell history.
 */
#[Signature('wasmsoft:create-admin {--name= : Full name} {--email= : Email address used to sign in}')]
#[Description('Create a dashboard administrator account')]
class CreateAdministrator extends Command
{
    public function handle(AuditLogger $audit): int
    {
        $name = $this->option('name') ?: text(
            label: 'Full name',
            required: true,
            validate: fn (string $value): ?string => $this->firstError('name', $value, ['string', 'max:255']),
        );

        $email = $this->option('email') ?: text(
            label: 'Email address',
            required: true,
            validate: fn (string $value): ?string => $this->firstError('email', $value, ['email', 'max:255', 'unique:users,email']),
        );

        if (($error = $this->firstError('email', $email, ['email', 'max:255', 'unique:users,email'])) !== null) {
            $this->components->error($error);

            return self::FAILURE;
        }

        $chosenPassword = password(
            label: 'Password',
            required: true,
            validate: fn (string $value): ?string => $this->firstError('password', $value, [Password::defaults()]),
            hint: 'At least 12 characters with upper- and lower-case letters and a number.',
        );

        password(
            label: 'Confirm password',
            required: true,
            validate: fn (string $value): ?string => hash_equals($chosenPassword, $value) ? null : 'The passwords do not match.',
        );

        $user = new User(['name' => $name, 'email' => $email, 'password' => $chosenPassword]);
        $user->role = Role::Administrator;
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->save();

        $audit->record(AuditEvent::UserCreated, $user, ['role' => Role::Administrator->value, 'source' => 'console']);

        info("Administrator {$user->email} created. Sign in at ".route('admin.login'));

        return self::SUCCESS;
    }

    /**
     * @param  list<mixed>  $rules
     */
    private function firstError(string $field, string $value, array $rules): ?string
    {
        $validator = Validator::make([$field => $value], [$field => ['required', ...$rules]]);

        return $validator->fails() ? $validator->errors()->first($field) : null;
    }
}
