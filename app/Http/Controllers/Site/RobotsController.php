<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * robots.txt. Outside production every crawler is turned away so staging
 * or preview deployments never end up in search results.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /up',
                '',
                'Sitemap: '.route('site.sitemap'),
            ]
            : [
                'User-agent: *',
                'Disallow: /',
            ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
