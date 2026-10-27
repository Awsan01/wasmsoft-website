<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\RendersSitePages;
use App\Seo\SeoMeta;
use Inertia\Response;

/**
 * Design system reference (registered in the local environment only, never
 * indexed): the tokens and shared components inside the real layout, to
 * review them left to right and right to left.
 */
class StyleguideController extends Controller
{
    use RendersSitePages;

    public function __invoke(): Response
    {
        return $this->sitePage('site/styleguide', [
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => 'Design system', 'url' => null],
            ],
        ], new SeoMeta(title: 'Design system · Wasmsoft', indexable: false));
    }
}
