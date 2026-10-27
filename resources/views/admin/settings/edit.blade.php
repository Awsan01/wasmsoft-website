<x-layouts.admin :title="__('admin.settings.title')" :breadcrumbs="[['label' => __('admin.settings.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.settings.title')" :description="__('admin.settings.description')">
        <x-slot:actions>
            <x-ui.button type="submit" form="settings-form" icon="check">{{ __('admin.actions.save') }}</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="settings-form" method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-6" novalidate>
        @csrf
        @method('PUT')

        <x-admin.section :title="__('admin.settings.sections.company')" :description="__('admin.settings.hints.company')">
            <x-admin.localized-input name="site_name" :label="__('admin.settings.fields.site_name')" :values="$localized['site_name']" maxlength="80" :required="$defaultLocale" />
            <x-admin.localized-input name="site_tagline" :label="__('admin.settings.fields.site_tagline')" :values="$localized['site_tagline']" maxlength="120" />
            <x-admin.localized-input
                name="site_description"
                type="textarea"
                :label="__('admin.settings.fields.site_description')"
                :values="$localized['site_description']"
                :hint="__('admin.settings.hints.site_description')"
                maxlength="300"
                :rows="3"
            />
        </x-admin.section>

        <x-admin.section :title="__('admin.settings.sections.brand')" :description="__('admin.settings.hints.brand')">
            <div class="grid gap-6 md:grid-cols-3">
                <x-admin.media-picker name="logo_id" :label="__('admin.settings.fields.logo_id')" :media="$images['logo_id']" :hint="__('admin.settings.hints.logo')" />
                <x-admin.media-picker name="favicon_id" :label="__('admin.settings.fields.favicon_id')" :media="$images['favicon_id']" :hint="__('admin.settings.hints.favicon')" />
                <x-admin.media-picker name="og_image_id" :label="__('admin.settings.fields.og_image_id')" :media="$images['og_image_id']" :hint="__('admin.settings.hints.og_image')" />
            </div>
        </x-admin.section>

        <x-admin.section :title="__('admin.settings.sections.contact')" :description="__('admin.settings.hints.contact')">
            <div class="grid gap-5 md:grid-cols-2">
                <x-ui.input type="email" name="contact_email" :label="__('admin.settings.fields.contact_email')" :value="$contact['email']" maxlength="254" autocomplete="off" dir="ltr" />
                <x-ui.input type="tel" name="contact_phone" :label="__('admin.settings.fields.contact_phone')" :value="$contact['phone']" maxlength="40" dir="ltr" />
            </div>
            <x-admin.localized-input name="contact_address" type="textarea" :label="__('admin.settings.fields.contact_address')" :values="$localized['contact_address']" maxlength="300" :rows="3" />
            <x-admin.localized-input name="contact_hours" type="textarea" :label="__('admin.settings.fields.contact_hours')" :values="$localized['contact_hours']" maxlength="200" />
            <div class="grid gap-5 md:grid-cols-2">
                <x-ui.input type="url" name="contact_map_url" :label="__('admin.settings.fields.contact_map_url')" :value="$contact['map_url']" :hint="__('admin.settings.hints.map_url')" placeholder="https://" maxlength="500" dir="ltr" />
                <x-ui.input type="email" name="notification_email" :label="__('admin.settings.fields.notification_email')" :value="$contact['notification_email']" :hint="__('admin.settings.hints.notification_email')" maxlength="254" autocomplete="off" dir="ltr" />
            </div>
        </x-admin.section>

        <x-admin.section :title="__('admin.settings.sections.social')" :description="__('admin.settings.hints.social')">
            <div class="grid gap-5 md:grid-cols-2">
                @foreach (\App\Support\SocialNetwork::cases() as $network)
                    <x-ui.input
                        type="url"
                        :name="'social['.$network->value.']'"
                        :label="$network->label()"
                        :value="$social[$network->value] ?? null"
                        :placeholder="'https://'.$network->hosts()[0].'/…'"
                        maxlength="255"
                        dir="ltr"
                    />
                @endforeach
            </div>
        </x-admin.section>

        <x-admin.section :title="__('admin.settings.sections.languages')" :description="__('admin.settings.hints.languages')">
            <div class="grid gap-6 md:grid-cols-2">
                <x-admin.checkbox-list name="enabled_locales" :label="__('admin.settings.fields.enabled_locales')" :options="$languages" :selected="$enabledLocales" />
                <x-ui.select
                    name="default_locale"
                    :label="__('admin.settings.fields.default_locale')"
                    :options="$languages"
                    :value="$defaultLocale"
                    :hint="__('admin.settings.hints.default_locale')"
                    required
                />
            </div>
        </x-admin.section>

        <x-admin.section :title="__('admin.settings.sections.footer')">
            <x-admin.localized-input name="footer_note" :label="__('admin.settings.fields.footer_note')" :values="$localized['footer_note']" :hint="__('admin.settings.hints.footer_note')" maxlength="200" />
        </x-admin.section>

        <div>
            <x-ui.button type="submit" icon="check">{{ __('admin.actions.save') }}</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
