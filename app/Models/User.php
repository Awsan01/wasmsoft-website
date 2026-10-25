<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Auth\Permission;
use App\Auth\Role;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A dashboard account. There is no public registration: administrators
 * create accounts and choose their role.
 *
 * `role` and `is_active` are deliberately not mass assignable; they are
 * only changed through the authorized user-management actions.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Role $role
 * @property string|null $locale
 * @property bool $is_active
 * @property Carbon|null $last_login_at
 */
#[Fillable(['name', 'email', 'password', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements Auditable, HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'viewer',
        'locale' => null,
        'is_active' => true,
        'last_login_at' => null,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Inactive accounts keep their history but lose every permission.
     */
    public function hasPermission(Permission $permission): bool
    {
        return $this->is_active && $this->role->grants($permission);
    }

    public function isAdministrator(): bool
    {
        return $this->is_active && $this->role === Role::Administrator;
    }

    /**
     * Language for notifications (password reset, invitations) and the dashboard.
     */
    public function preferredLocale(): ?string
    {
        return $this->locale;
    }

    public function auditLabel(): string
    {
        return "{$this->name} <{$this->email}>";
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Email addresses are stored trimmed and lower-cased so look-ups behave
     * the same on every database (SQLite compares case-sensitively).
     *
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value): string => Str::lower(trim($value)));
    }
}
