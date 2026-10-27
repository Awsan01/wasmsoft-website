<?php

namespace App\Http\Requests\Admin;

use App\Content\SystemPage;
use App\Http\Requests\Admin\Concerns\ValidatesLocalizedText;
use App\Models\MenuItem;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * A menu link or footer column. Destinations are limited to the site's own
 * pages and http(s) addresses; the menu of an existing item never changes.
 */
class MenuItemRequest extends FormRequest
{
    use ValidatesLocalizedText;

    public function authorize(): bool
    {
        $item = $this->item();

        return $item->exists
            ? ($this->user()?->can('update', $item) ?? false)
            : ($this->user()?->can('create', MenuItem::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'menu' => ['required', Rule::enum(MenuLocation::class)],
            'type' => ['required', Rule::enum(MenuLinkType::class)],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')
                ->where('menu', MenuLocation::Footer->value)
                ->where('type', MenuLinkType::Group->value)],
            'system_page' => ['nullable', 'required_if:type,'.MenuLinkType::System->value, Rule::enum(SystemPage::class)],
            'page_id' => ['nullable', 'required_if:type,'.MenuLinkType::Page->value, 'integer', Rule::exists('pages', 'id')->whereNull('system_key')],
            'url' => ['nullable', 'required_if:type,'.MenuLinkType::Url->value, 'string', 'max:500', 'url:https,http'],
            ...$this->localizedRules('label', 60),
            'opens_in_new_tab' => ['boolean'],
            'is_visible' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'menu' => __('admin.navigation.fields.menu'),
            'type' => __('admin.navigation.fields.type'),
            'parent_id' => __('admin.navigation.fields.parent_id'),
            'system_page' => __('admin.navigation.fields.system_page'),
            'page_id' => __('admin.navigation.fields.page_id'),
            'url' => __('admin.navigation.fields.url'),
            ...$this->localizedAttributes('label', __('admin.navigation.fields.label')),
            'opens_in_new_tab' => __('admin.navigation.fields.opens_in_new_tab'),
            'is_visible' => __('admin.blocks.visible_label'),
        ];
    }

    protected function prepareForValidation(): void
    {
        // An existing item stays in its menu.
        if ($this->item()->exists) {
            $this->merge(['menu' => $this->item()->menu->value]);
        }
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $menu = $this->menu();
            $type = $this->type();

            if ($type === MenuLinkType::Group && $menu !== MenuLocation::Footer) {
                $validator->errors()->add('type', __('validation.in', ['attribute' => __('admin.navigation.fields.type')]));
            }

            if ($menu === MenuLocation::Footer && $type !== MenuLinkType::Group && ! $this->filled('parent_id')) {
                $validator->errors()->add('parent_id', __('admin.navigation.group_needed'));
            }

            if (in_array($type, [MenuLinkType::Url, MenuLinkType::Group], true)) {
                $this->requireInSomeLanguage($validator, 'label');
            }

            $item = $this->item();

            if ($item->exists && $item->type === MenuLinkType::Group && $type !== MenuLinkType::Group && $item->children()->exists()) {
                $validator->errors()->add('type', __('admin.navigation.group_has_links'));
            }
        }];
    }

    /**
     * @return array<string, mixed>
     */
    public function itemAttributes(): array
    {
        $type = $this->type();
        $menu = $this->menu();

        return [
            'menu' => $menu,
            'type' => $type,
            'parent_id' => $menu === MenuLocation::Footer && $type !== MenuLinkType::Group ? $this->integer('parent_id') : null,
            'system_page' => $type === MenuLinkType::System ? $this->string('system_page')->toString() : null,
            'page_id' => $type === MenuLinkType::Page ? $this->integer('page_id') : null,
            'url' => $type === MenuLinkType::Url ? $this->string('url')->trim()->toString() : null,
            'label' => $this->localizedText('label'),
            'opens_in_new_tab' => $type === MenuLinkType::Url && $this->boolean('opens_in_new_tab'),
            'is_visible' => $this->boolean('is_visible'),
        ];
    }

    private function menu(): MenuLocation
    {
        return MenuLocation::from($this->string('menu')->toString());
    }

    private function type(): MenuLinkType
    {
        return MenuLinkType::from($this->string('type')->toString());
    }

    private function item(): MenuItem
    {
        $item = $this->route('item');

        return $item instanceof MenuItem ? $item : new MenuItem;
    }
}
