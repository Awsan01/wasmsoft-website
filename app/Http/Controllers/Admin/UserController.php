<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Invitations;
use App\Auth\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Localization\Locales;
use App\Models\User;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Dashboard accounts (administrators only). New users are invited by
 * email and choose their own password; accounts are deactivated rather
 * than deleted when someone leaves, which keeps their history readable.
 */
class UserController extends Controller
{
    private const STATUSES = ['active', 'inactive', 'invited'];

    public function __construct(
        private readonly AuditLogger $audit,
        private readonly Invitations $invitations,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $role = Role::tryFrom($request->string('role')->toString());
        $status = in_array($request->string('status')->toString(), self::STATUSES, true) ? $request->string('status')->toString() : null;
        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $users = User::query()
            ->when($role !== null, fn (Builder $query) => $query->where('role', $role))
            ->when($status === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn (Builder $query) => $query->where('is_active', false))
            ->when($status === 'invited', fn (Builder $query) => $query->whereNull('email_verified_at'))
            ->when($search !== '', fn (Builder $query) => Search::apply($query, ['name', 'email'], $search))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate((int) config('wasmsoft.pagination.admin'))
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'role' => $role,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return $this->form(new User);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'locale']));
        // Nobody knows this password; the invitation lets the person choose one.
        $user->password = Str::password(64);
        $user->role = $request->role();
        $user->is_active = true;

        DB::transaction(function () use ($user): void {
            $user->save();
            $this->audit->record(AuditEvent::UserCreated, $user, ['role' => $user->role->value]);
        });

        $this->invitations->send($user);

        return redirect()->route('admin.users.edit', $user)->with('status', __('admin.users.created'));
    }

    public function edit(User $user): View
    {
        Gate::authorize('view', $user);

        return $this->form($user);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $previousRole = $user->role;
        $wasActive = $user->is_active;

        $user->fill($request->safe()->only(['name', 'email', 'locale']));
        $changed = array_keys($user->getDirty());
        $user->role = $request->role();
        $user->is_active = $request->boolean('is_active');

        DB::transaction(function () use ($user, $changed, $previousRole, $wasActive): void {
            if (! $user->is_active && $wasActive) {
                // Signs out "remember me" cookies too.
                $user->setRememberToken(Str::random(60));
            }

            $user->save();

            if ($changed !== []) {
                $this->audit->record(AuditEvent::UserUpdated, $user, ['changed' => $changed]);
            }

            if ($user->role !== $previousRole) {
                $this->audit->record(AuditEvent::UserRoleChanged, $user, ['from' => $previousRole->value, 'to' => $user->role->value]);
            }

            if ($user->is_active !== $wasActive) {
                $this->audit->record($user->is_active ? AuditEvent::UserActivated : AuditEvent::UserDeactivated, $user);
            }
        });

        if (! $user->is_active && $wasActive) {
            $this->endSessions($user);
        }

        return redirect()->route('admin.users.edit', $user)->with('status', __('admin.content.saved'));
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        if ($user->isAdministrator() && ! UserRequest::otherActiveAdministratorExists($user)) {
            return redirect()->route('admin.users.edit', $user)->with('error', __('admin.users.last_administrator'));
        }

        DB::transaction(function () use ($user): void {
            // Recorded first, while the account can still be named.
            $this->audit->record(AuditEvent::UserDeleted, $user);
            $user->delete();
        });

        $this->endSessions($user);

        return redirect()->route('admin.users.index')->with('status', __('admin.content.deleted'));
    }

    public function invite(User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        if (! $this->invitations->isPending($user) || ! $user->is_active) {
            return redirect()->route('admin.users.edit', $user)->with('error', __('admin.users.invitation_not_needed'));
        }

        return $this->invitations->send($user)
            ? redirect()->route('admin.users.edit', $user)->with('status', __('admin.users.invitation_sent', ['email' => $user->email]))
            : redirect()->route('admin.users.edit', $user)->with('warning', __('admin.users.invitation_throttled'));
    }

    private function form(User $user): View
    {
        $locales = app(Locales::class);

        return view('admin.users.form', [
            'user' => $user,
            'roles' => collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => $role->label()])->all(),
            'languages' => array_combine($locales->supported(), array_map($locales->nativeName(...), $locales->supported())),
            'isSelf' => $user->exists && $user->is(auth()->user()),
            'pendingInvitation' => $user->exists && $this->invitations->isPending($user),
        ]);
    }

    /**
     * Sign the account out everywhere (database sessions).
     */
    private function endSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table((string) config('session.table', 'sessions'))->where('user_id', $user->getKey())->delete();
        }
    }
}
