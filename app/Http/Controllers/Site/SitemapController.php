<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Seo\Sitemap;
use Illuminate\Http\Response;

/**
 * XML sitemap of every public, published URL in every enabled locale.
 */
class SitemapController extends Controller
{
    public function __invoke(Sitemap $sitemap): Response
    {
        return response()
            ->view('site.sitemap', ['urls' => $sitemap->entries()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
