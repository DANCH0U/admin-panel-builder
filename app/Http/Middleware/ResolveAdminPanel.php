<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current admin panel (multi-panel support).
 *
 * Usage: middleware `panel:admin` or auto-detect from URL prefix.
 */
class ResolveAdminPanel
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, ?string $panel = null): Response
    {
        $key = $panel ?: $this->detectFromRequest($request) ?: config('admin.default', 'admin');
        $panels = config('admin.panels', []);

        if (!isset($panels[$key])) {
            abort(404, "Unknown admin panel [{$key}].");
        }

        app()->instance('admin.panel', $key);
        $request->attributes->set('admin.panel', $key);

        return $next($request);
    }

    protected function detectFromRequest(Request $request): ?string
    {
        $path = trim($request->path(), '/');

        foreach (config('admin.panels', []) as $key => $config) {
            $prefix = trim((string) ($config['prefix'] ?? $key), '/');
            if ($prefix === '') {
                continue;
            }
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $key;
            }
        }

        return null;
    }
}
