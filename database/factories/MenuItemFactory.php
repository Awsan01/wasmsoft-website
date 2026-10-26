<?php

namespace Database\Factories;

use App\Content\SystemPage;
use App\Models\MenuItem;
use App\Models\Page;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * A header link to a fixed page.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu' => MenuLocation::Header,
            'type' => MenuLinkType::System,
            'system_page' => SystemPage::Services,
            'is_visible' => true,
        ];
    }

    public function toPage(Page $page): static
    {
        return $this->state(fn (array $attributes) => ['type' => MenuLinkType::Page, 'system_page' => null, 'page_id' => $page->id]);
    }

    /**
     * @param  array<string, string>  $label
     */
    public function external(string $url, array $label): static
    {
        return $this->state(fn (array $attributes) => ['type' => MenuLinkType::Url, 'system_page' => null, 'url' => $url, 'label' => $label]);
    }

    /**
     * A footer column heading.
     *
     * @param  array<string, string>  $label
     */
    public function group(array $label): static
    {
        return $this->state(fn (array $attributes) => ['menu' => MenuLocation::Footer, 'type' => MenuLinkType::Group, 'system_page' => null, 'label' => $label]);
    }

    /**
     * A link inside a footer group.
     */
    public function in(MenuItem $group): static
    {
        return $this->state(fn (array $attributes) => ['menu' => MenuLocation::Footer, 'parent_id' => $group->id]);
    }
}
