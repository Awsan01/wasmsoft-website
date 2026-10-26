<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Permission;
use App\Content\ContentCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Localization\Locales;
use App\Models\Media;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\SocialNetwork;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Site-wide settings (administrators only): company details, brand images,
 * contact details, social links, languages and the footer. Secrets and
 * server configuration are never shown or edited here.
 */
class SettingsController extends Controller
{
    public function __construct(private readonly SiteSettings $settings) {}

    public function edit(Locales $locales): View
    {
        Gate::authorize(Permission::ManageSettings->value);

        $imageIds = array_map(fn (SettingKey $key): mixed => $this->settings->get($key), SettingsRequest::IMAGES);
        $images = Media::query()->images()->whereKey(array_values(array_filter($imageIds, is_numeric(...))))->get()->keyBy('id');

        $social = [];

        foreach (SocialNetwork::linksFrom($this->settings->get(SettingKey::SocialLinks)) as $link) {
            $social[$link['network']->value] = $link['url'];
        }

        $enabled = $locales->enabled();

        return view('admin.settings.edit', [
            'localized' => array_map(fn (array $definition): array => (array) $this->settings->get($definition[0]), SettingsRequest::LOCALIZED),
            'images' => array_map(fn (mixed $id): ?Media => is_numeric($id) ? $images->get((int) $id) : null, $imageIds),
            'social' => $social,
            'languages' => array_combine($locales->supported(), array_map($locales->nativeName(...), $locales->supported())),
            // After a failed submission, the languages that were ticked (possibly none).
            'enabledLocales' => request()->session()->hasOldInput() ? (array) old('enabled_locales', []) : $enabled,
            'defaultLocale' => $locales->default(),
            'contact' => [
                'email' => $this->settings->get(SettingKey::ContactEmail),
                'phone' => $this->settings->get(SettingKey::ContactPhone),
                'map_url' => $this->settings->get(SettingKey::ContactMapUrl),
                'notification_email' => $this->settings->get(SettingKey::InquiryNotificationEmail),
            ],
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
