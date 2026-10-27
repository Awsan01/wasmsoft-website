<?php

namespace App\Http\Responses;

use App\Http\Middleware\HandleSiteInertiaRequests;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * A page of the public site rendered from the dashboard (previews of
 * drafts): the public site's Inertia setup — root view, menus, footer,
 * interface strings — runs around it, so the preview looks exactly like
 * the page visitors will see. Nothing the dashboard shares reaches it.
 */
class SitePreview
{
    /**
     * @param  Closure(): InertiaResponse  $render
     */
    public function render(Request $request, Closure $render): Response
    {
        Inertia::flushShared();

        return app(HandleSiteInertiaRequests::class)->handle($request, fn (Request $request): Response => $render()->toResponse($request));
    }
}
