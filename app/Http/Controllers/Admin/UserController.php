<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Invitations;
use App\Auth\Role;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\UserRequest;
use App\Localization\Locales;
use App\Models\User;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Response;

/**
 * Dashboard accounts (administrators only). New users are invited by
 * email and choose their own password; accounts are deactivated rather
 * than deleted when someone leaves, which keeps their history readable.
 */
class UserController extends Controller
{
    use RendersAdminPages;

    private const STATUSES = ['active', 'inactive', 'invited'];

    public function __construct(
        private readonly AuditLogger $audit,
        private readonly Invitations $invitations,
        private readonly AdminPresenter $present,
    ) {}

    public function index(Request $request): Response
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

        $statuses = [];

        foreach (self::STATUSES as $key) {
            $statuses[$key] = __('admin.users.status.'.$key);
        }

        return $this->adminPage('users/index', __('admin.users.title'), [
            'description' => __('admin.users.description'),
            'createUrl' => Gate::allows('create', User::class) ? route('admin.users.create') : null,
            'users' => $users->getCollection()->map(function (User $account) use ($request): array {
                [$status, $tone] = match (true) {
                    ! $account->is_active => ['inactive', 'neutral'],
                    $account->email_verified_at === null => ['invited', 'warning'],
                    default => ['active', 'success'],
                };

                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'email' => $account->email,
                    'role' => $account->role->label(),
                    'status' => ['label' => __('admin.users.status.'.$status), 'tone' => $tone],
                    'lastLogin' => $this->present->time($account->last_login_at),
                    'isSelf' => $account->is($request->user()),
                    'editUrl' => route('admin.users.edit', $account),
                ];
            })->values()->all(),
            'pagination' => $this->present->pagination($users),
            'filters' => ['q' => $search, 'role' => $role->value ?? '', 'status' => $status ?? ''],
            'roleOptions' => $this->present->options($this->roleOptions()),
            'statusOptions' => $this->present->options($statuses),
            'indexUrl' => route('admin.users.index'),
        ]);
    }

    public function create(): Response
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

    public function edit(User $user): Response
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

    private function form(User $user): Response
    {
        $locales = app(Locales::class);
        $isNew = ! $user->exists;
        $isSelf = ! $isNew && $user->is(request()->user());
        $title = $isNew ? __('admin.users.new') : $user->name;

        return $this->adminPage('users/form', $title, [
            'description' => $isNew ? __('admin.users.invite_hint') : null,
            'isSelf' => $isSelf,
            'submit' => $this->present->formAction($user, 'admin.users'),
            'form' => [
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'locale' => (string) $user->locale,
                'role' => $user->role->value ?? Role::Viewer->value,
                // New accounts are active; your own account stays active.
                ...($isNew ? [] : ['is_active' => $user->is_active]),
            ],
            'roles' => array_map(fn (Role $role): array => [
                'value' => $role->value,
                'label' => $role->label(),
                'description' => $role->description(),
            ], Role::cases()),
            'languageOptions' => $this->present->options(array_combine($locales->supported(), array_map($locales->nativeName(...), $locales->supported()))),
            'inviteUrl' => ! $isNew && $user->is_active && $this->invitations->isPending($user) ? route('admin.users.invite', $user) : null,
            'destroy' => ! $isNew && Gate::allows('delete', $user)
                ? $this->present->deletion(route('admin.users.destroy', $user), $user->name, __('admin.users.delete_text'))
                : null,
        ], [
            ['label' => __('admin.users.title'), 'url' => route('admin.users.index')],
            ['label' => $title, 'url' => null],
        ]);
    }

    /**
     * @return array<string, string> role => name
     */
    private function roleOptions(): array
    {
        $roles = [];

        foreach (Role::cases() as $role) {
            $roles[$role->value] = $role->label();
        }

        return $roles;
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
