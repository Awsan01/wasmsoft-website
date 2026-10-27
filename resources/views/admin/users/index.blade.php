<x-layouts.admin :title="__('admin.users.title')" :breadcrumbs="[['label' => __('admin.users.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.users.title')" :description="__('admin.users.description')">
        @can('create', \App\Models\User::class)
            <x-slot:actions>
                <x-ui.button :href="route('admin.users.create')" icon="user-plus">{{ __('admin.users.new') }}</x-ui.button>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.users.index')" :search="$search" :active="$search !== '' || $role !== null || $status !== null">
        <x-ui.select
            name="role"
            :label="__('admin.users.fields.role')"
            :value="$role?->value"
            :placeholder="__('admin.users.all_roles')"
            :options="collect(\App\Auth\Role::cases())->mapWithKeys(fn ($role) => [$role->value => $role->label()])->all()"
            wrapper-class="sm:w-48"
        />
        <x-ui.select
            name="status"
            :label="__('admin.fields.status')"
            :value="$status"
            :placeholder="__('admin.users.all_statuses')"
            :options="['active' => __('admin.users.status.active'), 'inactive' => __('admin.users.status.inactive'), 'invited' => __('admin.users.status.invited')]"
            wrapper-class="sm:w-56"
        />
    </x-admin.filters>

    @if ($users->isEmpty())
        <x-ui.empty-state :title="__('admin.users.empty_title')" icon="users" />
    @else
        <x-ui.table :caption="__('admin.users.title')">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.users.fields.name') }}</th>
                    <th scope="col">{{ __('admin.users.fields.role') }}</th>
                    <th scope="col">{{ __('admin.fields.status') }}</th>
                    <th scope="col">{{ __('admin.users.last_login') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $account)
                    @php
                        $status = match (true) {
                            ! $account->is_active => ['inactive', 'neutral'],
                            $account->email_verified_at === null => ['invited', 'warning'],
                            default => ['active', 'success'],
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="grid gap-0.5">
                                <span class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $account) }}" class="font-semibold text-ink hover:text-primary hover:underline"><bdi>{{ $account->name }}</bdi></a>
                                    @if ($account->is(auth()->user()))
                                        <x-ui.badge tone="primary">{{ __('admin.users.you') }}</x-ui.badge>
                                    @endif
                                </span>
                                <span class="text-sm text-muted" dir="ltr">{{ $account->email }}</span>
                            </div>
                        </td>
                        <td>{{ $account->role->label() }}</td>
                        <td><x-ui.badge :tone="$status[1]" dot>{{ __('admin.users.status.'.$status[0]) }}</x-ui.badge></td>
                        <td class="whitespace-nowrap text-muted">
                            @if ($account->last_login_at)
                                <x-ui.time :value="$account->last_login_at" format="relative" />
                            @else
                                {{ __('admin.users.never') }}
                            @endif
                        </td>
                        <td class="text-end">
                            <x-ui.button :href="route('admin.users.edit', $account)" variant="outline" size="sm" icon="pencil">
                                {{ __('admin.actions.edit') }}<span class="sr-only">: <bdi>{{ $account->name }}</bdi></span>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        {{ $users->links() }}
    @endif
</x-layouts.admin>
