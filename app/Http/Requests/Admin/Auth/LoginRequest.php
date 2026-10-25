<?php

namespace App\Http\Requests\Admin\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

class LoginRequest extends FormRequest
{
    /** Failed attempts allowed per email address and IP before a lockout. */
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 300;

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Attempt to authenticate. Deactivated accounts are treated exactly like
     * wrong credentials so the response never reveals which accounts exist.
     *
     * @throws ValidationException
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'email' => Str::lower($this->string('email')->trim()->toString()),
            'password' => $this->string('password')->toString(),
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), self::LOCKOUT_SECONDS);

            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($this->throttleKey());

        $user = Auth::user();

        if (! $user instanceof User) {
            throw new LogicException('The web guard must authenticate App\Models\User instances.');
        }

        return $user;
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', ['seconds' => $seconds, 'minutes' => (int) ceil($seconds / 60)]),
        ]);
    }

    private function throttleKey(): string
    {
        return 'login-attempts|'.Str::transliterate(Str::lower($this->string('email')->trim()->toString()).'|'.$this->ip());
    }
}
