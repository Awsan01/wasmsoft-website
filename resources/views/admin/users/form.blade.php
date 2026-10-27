@php
    $isNew = ! $user->exists;
    $title = $isNew ? __('admin.users.new') : $user->name;
    $currentRole = \App\View\FormField::old('role', $user->role?->value ?? \App\Auth\Role::Viewer->value);
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => __('admin.users.title'), 'url' => route('admin.users.index')],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" :description="$isNew ? __('admin.users.invite_hint') : null" />

    <form method="POST" action="{{ $isNew ? route('admin.users.store') : route('admin.users.update', $user) }}" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="grid min-w-0 content-start gap-6">
                <x-admin.section :title="__('admin.users.account')">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-ui.input name="name" :label="__('admin.users.fields.name')" :value="$user->name" maxlength="255" autocomplete="off" required />
                        <x-ui.input type="email" name="email" :label="__('admin.users.fields.email')" :value="$user->email" maxlength="255" autocomplete="off" dir="ltr" required />
                    </div>
                    <x-ui.select
                        name="locale"
                        :label="__('admin.users.fields.locale')"
                        :value="$user->locale"
                        :placeholder="__('admin.users.browser_language')"
                        :options="$languages"
                        wrapper-class="md:w-1/2"
                    />
                </x-admin.section>

                <x-admin.section :title="__('admin.users.access')">
                    <fieldset class="grid gap-3" @if ($error = \App\View\FormField::error('role')) aria-describedby="field-role-error" @endif>
                        <legend class="mb-1 text-sm font-semibold text-ink">{{ __('admin.users.fields.role') }} <span class="text-error" aria-hidden="true">*</span></legend>
                        @foreach (\App\Auth\Role::cases() as $role)
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-border p-4 transition-base hover:border-purple-300 has-checked:border-primary has-checked:bg-lavender">
                                <input type="radio" name="role" value="{{ $role->value }}" @checked($currentRole === $role->value) class="mt-1 size-4 shrink-0 accent-primary" required>
                                <span class="grid gap-0.5">
                                    <span class="text-sm font-semibold text-ink">{{ $role->label() }}</span>
                                    <span class="text-sm text-muted">{{ $role->description() }}</span>
                                </span>
                            </label>
                        @endforeach
                        @if ($error)
                            <p id="field-role-error" class="flex items-start gap-1.5 text-sm font-medium text-error">
                                <x-ui.icon name="alert-circle" :size="16" class="mt-[0.2rem]" />
                                <span>{{ $error }}</span>
                            </p>
                        @endif
                    </fieldset>
                </x-admin.section>
            </div>

            <div class="grid content-start gap-6">
                <x-admin.section>
                    @unless ($isNew)
                        @if ($isSelf)
                            <input type="hidden" name="is_active" value="1">
                            <p class="text-sm text-muted">{{ __('admin.users.self_active') }}</p>
                        @else
                            <x-ui.checkbox name="is_active" :label="__('admin.users.active_label')" :hint="__('admin.users.active_hint')" :checked="$user->is_active" />
                        @endif
                    @endunless

                    <x-ui.button type="submit" :icon="$isNew ? 'send' : 'check'" block>
                        {{ $isNew ? __('admin.users.create_and_invite') : __('admin.actions.save') }}
                    </x-ui.button>
                </x-admin.section>
            </div>
        </div>
    </form>

    @if ($pendingInvitation && $user->is_active)
        <x-admin.section :title="__('admin.users.invitation')" :description="__('admin.users.invitation_pending')">
            <form method="POST" action="{{ route('admin.users.invite', $user) }}">
                @csrf
                <x-ui.button type="submit" variant="outline" icon="send">{{ __('admin.users.invite_again') }}</x-ui.button>
            </form>
        </x-admin.section>
    @endif

    @if (! $isNew && Gate::allows('delete', $user))
        <x-admin.section :title="__('admin.content.danger_title')" :description="__('admin.users.delete_text')">
            <x-admin.delete-button :action="route('admin.users.destroy', $user)" :name="$user->name" class="justify-self-start" />
        </x-admin.section>
    @endif
</x-layouts.admin>
