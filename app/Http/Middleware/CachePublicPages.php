<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marks successful public GET responses as cacheable by shared caches
 * (CDN / reverse proxy) for a short time, with an ETag so browsers can
 * revalidate cheaply. Browsers themselves always revalidate (max-age=0),
 * so edits appear immediately for visitors without a shared cache.
 *
 * Only applied to the session-less public routes: responses that set
 * cookies, errors and redirects are never marked public.
 */
class CachePublicPages
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $seconds = (int) config('wasmsoft.http_cache_seconds');

        if (! $request->isMethodCacheable()
            || $response->getStatusCode() !== 200
            || $response->headers->getCookies() !== []
            || $seconds <= 0) {
            return $response;
        }

        $response->setPublic();
        $response->setMaxAge(0);
        $response->setSharedMaxAge($seconds);

        $content = $response->getContent();

        // HEAD responses reach this point with their body already removed,
        // so only GET bodies are hashed (otherwise HEAD would report another ETag).
        if ($request->isMethod('GET') && $content !== false && $response->getEtag() === null) {
            $response->setEtag(hash('xxh128', $content));
        }

        $response->isNotModified($request);

        return $response;
    }
}
