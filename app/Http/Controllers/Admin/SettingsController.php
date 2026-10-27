<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Permission;
use App\Content\ContentCache;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\SettingsRequest;
use App\Localization\Locales;
use App\Models\Media;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\SocialNetwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

/**
 * Site-wide settings (administrators only): company details, brand images,
 * contact details, social links, languages and the footer. Secrets and
 * server configuration are never shown or edited here.
 */
class SettingsController extends Controller
{
    use RendersAdminPages;

    public function __construct(private readonly SiteSettings $settings) {}

    public function edit(Locales $locales, AdminPresenter $present): Response
    {
        Gate::authorize(Permission::ManageSettings->value);

        $imageIds = array_map(fn (SettingKey $key): mixed => $this->settings->get($key), SettingsRequest::IMAGES);
        $images = Media::query()->images()->whereKey(array_values(array_filter($imageIds, is_numeric(...))))->get()->keyBy('id');

        $social = [];

        foreach (SocialNetwork::cases() as $network) {
            $social[$network->value] = '';
        }

        foreach (SocialNetwork::linksFrom($this->settings->get(SettingKey::SocialLinks)) as $link) {
            $social[$link['network']->value] = $link['url'];
        }

        $localized = [];

        foreach (SettingsRequest::LOCALIZED as $field => [$key]) {
            $localized[$field] = $present->localized((array) $this->settings->get($key));
        }

        return $this->adminPage('settings/edit', __('admin.settings.title'), [
            'description' => __('admin.settings.description'),
            'submit' => ['url' => route('admin.settings.update'), 'method' => 'put'],
            'form' => [
                ...$localized,
                'images' => array_map(fn (mixed $id): ?array => $present->media(is_numeric($id) ? $images->get((int) $id) : null), $imageIds),
                'contact_email' => (string) $this->settings->get(SettingKey::ContactEmail),
                'contact_phone' => (string) $this->settings->get(SettingKey::ContactPhone),
                'contact_map_url' => (string) $this->settings->get(SettingKey::ContactMapUrl),
                'notification_email' => (string) $this->settings->get(SettingKey::InquiryNotificationEmail),
                'social' => $social,
                'enabled_locales' => $locales->enabled(),
                'default_locale' => $locales->default(),
            ],
            'networks' => array_map(fn (SocialNetwork $network): array => [
                'value' => $network->value,
                'label' => $network->label(),
                'placeholder' => 'https://'.$network->hosts()[0].'/…',
            ], SocialNetwork::cases()),
            'languageOptions' => $present->options(array_combine($locales->supported(), array_map($locales->nativeName(...), $locales->supported()))),
        ]);
    }

    public function update(SettingsRequest $request, ContentCache $cache, AuditLogger $audit): RedirectResponse
    {
        $values = $request->values();
        $changed = array_values(array_filter(
            array_keys($values),
            fn (string $key): bool => $this->settings->get(SettingKey::from($key)) !== $values[$key],
        ));

        if ($changed !== []) {
            DB::transaction(function () use ($values, $changed, $audit): void {
                $this->settings->put($values);
                // Which settings changed, not their values.
                $audit->record(AuditEvent::SettingsUpdated, properties: ['changed' => $changed]);
            });

            $cache->flush();
        }

        return redirect()->route('admin.settings.edit')->with('status', __('admin.content.saved'));
    }
}
