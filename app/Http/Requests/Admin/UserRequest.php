<?php

namespace App\Http\Requests\Admin;

use App\Auth\Role;
use App\Localization\Locales;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Create or update a dashboard account (administrators only). Passwords
 * are never set here: new users choose theirs through an invitation.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = $this->account();

        return $account->exists
            ? ($this->user()?->can('update', $account) ?? false)
            : ($this->user()?->can('create', User::class) ?? false);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($email = $this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($email))]);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'email:rfc,strict', Rule::unique('users', 'email')->ignore($this->account()->id)],
            'role' => ['required', Rule::enum(Role::class)],
            'locale' => ['nullable', 'string', Rule::in(app(Locales::class)->supported())],
        ];

        if ($this->account()->exists) {
            $rules['is_active'] = ['boolean'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('admin.users.fields.name'),
            'email' => __('admin.users.fields.email'),
            'role' => __('admin.users.fields.role'),
            'locale' => __('admin.users.fields.locale'),
            'is_active' => __('admin.users.fields.is_active'),
        ];
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $account = $this->account();

            if (! $account->exists || $validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->boolean('is_active') && $this->user()?->is($account)) {
                $validator->errors()->add('is_active', __('admin.users.cannot_deactivate_self'));
            }

            $staysAdministrator = $this->role() === Role::Administrator && $this->boolean('is_active');

            if ($account->isAdministrator() && ! $staysAdministrator && ! self::otherActiveAdministratorExists($account)) {
                $validator->errors()->add('role', __('admin.users.last_administrator'));
            }
        }];
    }

    public function role(): Role
    {
        return Role::from($this->string('role')->toString());
    }

    /**
     * Whether an active administrator other than $account exists.
     */
    public static function otherActiveAdministratorExists(User $account): bool
    {
        return User::query()
            ->whereKeyNot($account->getKey())
            ->where('role', Role::Administrator)
            ->where('is_active', true)
            ->exists();
    }

    private function account(): User
    {
        $account = $this->route('user');

        return $account instanceof User ? $account : new User;
    }
}
