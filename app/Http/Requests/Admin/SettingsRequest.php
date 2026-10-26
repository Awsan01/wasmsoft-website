<?php

namespace App\Http\Requests\Admin;

use App\Auth\Permission;
use App\Localization\Locales;
use App\Media\MediaKind;
use App\Settings\SettingKey;
use App\Support\SocialNetwork;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The website settings form (administrators only). Every field maps to a
 * SettingKey; nothing else can be written through it.
 */
class SettingsRequest extends FormRequest
{
    /**
     * Localized text fields: form field => [setting, maximum length].
     *
     * @var array<string, array{SettingKey, int}>
     */
    public const LOCALIZED = [
        'site_name' => [SettingKey::SiteName, 80],
        'site_tagline' => [SettingKey::SiteTagline, 120],
        'site_description' => [SettingKey::SiteDescription, 300],
        'contact_address' => [SettingKey::ContactAddress, 300],
        'contact_hours' => [SettingKey::ContactHours, 200],
        'footer_note' => [SettingKey::FooterNote, 200],
    ];

    /**
     * Image fields: form field => setting.
     *
     * @var array<string, SettingKey>
     */
    public const IMAGES = [
        'logo_id' => SettingKey::LogoMediaId,
        'favicon_id' => SettingKey::FaviconMediaId,
        'og_image_id' => SettingKey::OgImageMediaId,
    ];

    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ManageSettings->value) ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $locales = $this->locales()->supported();
        $default = $this->input('default_locale');
        $image = Rule::exists('media', 'id')->where('kind', MediaKind::Image->value);
        $rules = [];

        foreach (self::LOCALIZED as $field => [, $max]) {
            $rules[$field] = ['nullable', 'array:'.implode(',', $locales)];
            $rules[$field.'.*'] = ['nullable', 'string', 'max:'.$max];
        }

        // The company needs a name, at least in the default language.
        if (is_string($default) && in_array($default, $locales, true)) {
            $rules["site_name.{$default}"] = ['required', 'string', 'max:80'];
        }

        foreach (array_keys(self::IMAGES) as $field) {
            $rules[$field] = ['nullable', 'integer', $image];
        }

        return [
            ...$rules,
            'default_locale' => ['required', Rule::in($locales)],
            'enabled_locales' => ['required', 'array', 'min:1'],
            'enabled_locales.*' => ['string', 'distinct', Rule::in($locales)],
            'contact_email' => ['nullable', 'string', 'max:254', 'email:rfc,strict'],
            'contact_phone' => ['nullable', 'string', 'max:40', 'regex:/^\+?[0-9 ().\-]{6,40}$/'],
            'contact_map_url' => ['nullable', 'string', 'max:500', 'url:https'],
            'notification_email' => ['nullable', 'string', 'max:254', 'email:rfc,strict'],
            'social' => ['nullable', 'array:'.implode(',', array_column(SocialNetwork::cases(), 'value'))],
            'social.*' => ['nullable', 'string', 'max:255', 'url:https'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (array_keys(self::LOCALIZED) as $field) {
            foreach ($this->locales()->supported() as $locale) {
                $attributes["{$field}.{$locale}"] = __('admin.translations.field', [
                    'field' => __("admin.settings.fields.{$field}"),
                    'language' => $this->locales()->nativeName($locale),
                ]);
            }
        }

        foreach (SocialNetwork::cases() as $network) {
            $attributes["social.{$network->value}"] = $network->label();
        }

        foreach (['logo_id', 'favicon_id', 'og_image_id', 'default_locale', 'enabled_locales', 'contact_email', 'contact_phone', 'contact_map_url', 'notification_email'] as $field) {
            $attributes[$field] = __("admin.settings.fields.{$field}");
        }

        return $attributes;
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $enabled = (array) $this->input('enabled_locales', []);

            if (! $validator->errors()->has('default_locale') && ! in_array($this->input('default_locale'), $enabled, true)) {
                $validator->errors()->add('enabled_locales', __('admin.settings.default_must_be_enabled'));
            }

            foreach (SocialNetwork::cases() as $network) {
                $url = $this->input("social.{$network->value}");
                $key = "social.{$network->value}";

                if (is_string($url) && $url !== '' && ! $validator->errors()->has($key) && ! $network->allows($url)) {
                    $validator->errors()->add($key, __('admin.settings.social_host', ['network' => $network->label(), 'host' => $network->hosts()[0]]));
                }
            }
        }];
    }

    /**
     * The validated settings: setting key value => value.
     *
     * @return array<string, mixed>
     */
    public function values(): array
    {
        $values = [];

        foreach (self::LOCALIZED as $field => [$key]) {
            $texts = array_filter((array) $this->validated($field, []), fn (mixed $text): bool => is_string($text) && trim($text) !== '');
            $values[$key->value] = $texts === [] ? null : array_map(trim(...), $texts);
        }

        foreach (self::IMAGES as $field => $key) {
            $values[$key->value] = $this->filled($field) ? $this->integer($field) : null;
        }

        $enabled = (array) $this->validated('enabled_locales');
        $values[SettingKey::DefaultLocale->value] = $this->validated('default_locale');
        // Stored in the order of the language selectors.
        $values[SettingKey::EnabledLocales->value] = array_values(array_intersect($this->locales()->supported(), $enabled));

        $values[SettingKey::ContactEmail->value] = $this->validated('contact_email');
        $values[SettingKey::ContactPhone->value] = $this->validated('contact_phone');
        $values[SettingKey::ContactMapUrl->value] = $this->validated('contact_map_url');
        $values[SettingKey::InquiryNotificationEmail->value] = $this->validated('notification_email');

        $links = [];

        foreach (SocialNetwork::cases() as $network) {
            $url = $this->validated("social.{$network->value}");

            if (is_string($url) && $url !== '') {
                $links[] = ['network' => $network->value, 'url' => $url];
            }
        }

        $values[SettingKey::SocialLinks->value] = $links;

        return $values;
    }

    private function locales(): Locales
    {
        return app(Locales::class);
    }
}
