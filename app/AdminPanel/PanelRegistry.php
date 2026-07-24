<?php

namespace App\AdminPanel;

use Closure;
use InvalidArgumentException;
use RuntimeException;

class PanelRegistry
{
    /** @var array<string, Panel> */
    protected static array $panels = [];

    /**
     * @param  Closure(Panel): void  $configure
     */
    public static function register(string $id, Closure $configure): Panel
    {
        $panel = new Panel($id);
        $configure($panel);
        static::$panels[$id] = $panel;

        return $panel;
    }

    public static function get(string $id): Panel
    {
        if (! isset(static::$panels[$id])) {
            throw new InvalidArgumentException("Unknown admin panel [{$id}].");
        }

        return static::$panels[$id];
    }

    public static function has(string $id): bool
    {
        return isset(static::$panels[$id]);
    }

    /**
     * @return array<string, Panel>
     */
    public static function all(): array
    {
        return static::$panels;
    }

    public static function default(): Panel
    {
        $default = (string) config('admin.default', 'admin');

        if (static::has($default)) {
            return static::get($default);
        }

        $first = reset(static::$panels);

        if ($first instanceof Panel) {
            return $first;
        }

        throw new RuntimeException('No admin panels have been registered.');
    }

    /**
     * Resolve a panel by registry id or URL prefix.
     */
    public static function resolve(string $idOrPrefix): Panel
    {
        $needle = trim($idOrPrefix, '/');

        if (static::has($needle)) {
            return static::get($needle);
        }

        foreach (static::$panels as $panel) {
            if ($panel->getPrefix() === $needle) {
                return $panel;
            }
        }

        throw new InvalidArgumentException("Unknown admin panel [{$idOrPrefix}].");
    }

    public static function findByPrefix(string $prefix): ?Panel
    {
        $prefix = trim($prefix, '/');

        foreach (static::$panels as $panel) {
            if ($panel->getPrefix() === $prefix) {
                return $panel;
            }
        }

        return null;
    }

    /**
     * @internal Testing / re-registration helpers
     */
    public static function flush(): void
    {
        static::$panels = [];
    }
}
