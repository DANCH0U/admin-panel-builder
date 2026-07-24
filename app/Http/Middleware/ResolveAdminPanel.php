<?php

namespace App\Http\Middleware;

use App\AdminPanel\PanelRegistry;
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
        $key = $panel ?: $this->detectFromRequest($request)?->getId() ?: config('admin.default', 'admin');

        if (! PanelRegistry::has($key)) {
            abort(404, "Unknown admin panel [{$key}].");
        }

        app()->instance('admin.panel', $key);
        $request->attributes->set('admin.panel', $key);

        return $next($request);
    }

    protected function detectFromRequest(Request $request): ?\App\AdminPanel\Panel
    {
        $path = trim($request->path(), '/');

        foreach (PanelRegistry::all() as $panel) {
            $prefix = $panel->getPrefix();
            if ($prefix === '') {
                continue;
            }
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $panel;
            }
        }

        return null;
    }
}
