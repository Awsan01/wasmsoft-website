<?php

namespace App\Http\Middleware;

use App\Security\InlineStyles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds security headers to every response.
 *
 * The Content Security Policy only allows same-origin scripts and styles
 * (the Vite bundles) plus the inline style blocks the page registered by
 * hash (App\Security\InlineStyles — the web font rules). Inline scripts,
 * event handlers, style attributes and third-party code are blocked.
 * JSON-LD blocks are data, not script, so the policy does not apply to them.
 */
class SecurityHeaders
{
    public function __construct(private readonly InlineStyles $inlineStyles) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Sent by PHP itself (expose_php); it only advertises the PHP version.
        header_remove('X-Powered-By');

        $headers = $response->headers;
        $headers->set('Content-Security-Policy', $this->contentSecurityPolicy($request));
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), browsing-topics=()');

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(Request $request): string
    {
        $self = "'self'";
        $script = [$self];
        $style = [$self, ...$this->inlineStyles->sources()];
        $font = [$self];
        $connect = [$self];
        $img = [$self, 'data:', 'blob:', ...$this->mediaOrigins($request)];

        // The Vite dev server (npm run dev) serves assets from its own origin
        // and injects <style> elements at runtime; allow it in development only.
        // (Browsers ignore 'unsafe-inline' next to hashes, so hashes are dropped.)
        if (Vite::isRunningHot() && ($devServer = $this->devServerOrigin()) !== null) {
            $script[] = $devServer;
            $style = [$self, $devServer, "'unsafe-inline'"];
            $font[] = $devServer;
            $img[] = $devServer;
            $connect[] = $devServer;
            $connect[] = preg_replace('/^http/', 'ws', $devServer);
        }

        $directives = [
            'default-src' => [$self],
            'script-src' => $script,
            'style-src' => $style,
            'img-src' => $img,
            'font-src' => $font,
            'connect-src' => $connect,
            'media-src' => [$self],
            'object-src' => ["'none'"],
            'frame-src' => ["'none'"],
            'base-uri' => [$self],
            'form-action' => [$self],
            'frame-ancestors' => ["'none'"],
        ];

        $policy = collect($directives)
            ->map(fn (array $sources, string $directive): string => $directive.' '.implode(' ', array_unique($sources)))
            ->implode('; ');

        return $request->isSecure() ? $policy.'; upgrade-insecure-requests' : $policy;
    }

    private function devServerOrigin(): ?string
    {
        $hotFile = Vite::hotFile();
        $parts = is_file($hotFile) ? parse_url(trim((string) file_get_contents($hotFile))) : false;

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $host = str_contains($parts['host'], ':') && ! str_starts_with($parts['host'], '[') ? "[{$parts['host']}]" : $parts['host'];

        return $parts['scheme'].'://'.$host.(isset($parts['port']) ? ':'.$parts['port'] : '');
    }

    /**
     * The origin serving uploaded media when it differs from the page's own
     * (an S3 bucket or CDN, or an APP_URL host that differs from the one in
     * the browser, as with localhost and 127.0.0.1 in development).
     *
     * @return list<string>
     */
    private function mediaOrigins(Request $request): array
    {
        $url = config('filesystems.disks.'.config('wasmsoft.media.disk').'.url');
        $parts = is_string($url) ? parse_url($url) : false;

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return [];
        }

        $origin = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        return $origin === $request->getSchemeAndHttpHost() ? [] : [$origin];
    }
}
