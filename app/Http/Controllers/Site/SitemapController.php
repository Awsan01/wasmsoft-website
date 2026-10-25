<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Localization\Locales;
use Illuminate\Http\Response;

/**
 * XML sitemap of every public, published URL in every enabled locale.
 */
class SitemapController extends Controller
{
    public function __invoke(Locales $locales): Response
    {
        $urls = array_map(
            fn (string $locale): array => ['loc' => route('site.home', ['locale' => $locale])],
            $locales->enabled(),
        );

        return response()
            ->view('site.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
