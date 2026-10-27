<?php

namespace App\Http\Responses;

use App\Http\Controllers\Site\Concerns\RendersSitePages;
use App\Http\Middleware\HandleSiteInertiaRequests;
use App\Localization\Locales;
use App\Seo\SeoMeta;
use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Client errors on the public site (a missing page, a forbidden or expired
 * one, too many requests) shown as a page of the site, with its menus, in
 * the language of the address or of the browser — also when the visitor
 * arrives there through a link inside the site.
 *
 * Server errors and maintenance keep the self-contained error views
 * (resources/views/errors), which need neither the database nor
 * JavaScript; so do the dashboard, answers to forms and JSON requests.
 * Should building the page fail, the self-contained view is used as well.
 */
class SiteErrorPage
{
    use RendersSitePages;

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
    ) {}

    public function render(Response $response, Request $request): ?Response
    {
        $status = $response->getStatusCode();

        if ($status < 400 || $status >= 500 || ! $request->isMethod('GET') || $request->is('admin', 'admin/*', 'up')) {
            return null;
        }

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return null;
        }

        try {
            $segment = $request->segment(1);
            $locale = $this->locales->isEnabled($segment) ? (string) $segment : $this->locales->negotiate($request);

            app()->setLocale($locale);
            URL::defaults(['locale' => $locale]);

            $middleware = app(HandleSiteInertiaRequests::class);
            Inertia::version(fn (): ?string => $middleware->version($request));
            Inertia::setRootView($middleware->rootView($request));
            Inertia::share($middleware->share($request));

            foreach ($middleware->shareOnce($request) as $key => $prop) {
                Inertia::share($key, $prop);
            }

            $texts = in_array($status, [403, 404, 419, 429], true) ? (string) $status : '4xx';
            $title = __("site.errors.{$texts}.title");

            $page = $this->sitePage('site/error', [
                'status' => $status,
                'title' => $title,
                'message' => __("site.errors.{$texts}.message"),
            ], new SeoMeta(title: $title.' · '.$this->brand->name(), indexable: false))->toResponse($request);

            return $page->setStatusCode($status);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
