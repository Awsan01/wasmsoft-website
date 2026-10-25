<?php

namespace App\View\Components\Admin;

use App\Navigation\AdminNavigation;
use App\Support\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Dashboard navigation. Rendered twice per page: as the static sidebar on
 * large screens and inside the modal drawer on small ones (`drawer`).
 */
class Sidebar extends Component
{
    public function __construct(
        private readonly AdminNavigation $navigation,
        private readonly Brand $brand,
        public bool $drawer = false,
    ) {}

    public function render(): View
    {
        return view('components.admin.sidebar', [
            'groups' => $this->navigation->groups(request()->user()),
            'siteName' => $this->brand->name(),
            'logoUrl' => $this->brand->logoUrl(),
            'idPrefix' => $this->drawer ? 'drawer' : 'sidebar',
        ]);
    }
}
