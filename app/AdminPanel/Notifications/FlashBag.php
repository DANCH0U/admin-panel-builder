<?php

namespace App\AdminPanel\Notifications;

/**
 * Collects session flash into a normalized notifications list for Inertia.
 */
class FlashBag
{
    public const LEGACY_KEYS = [
        'success',
        'info',
        'warning',
        'danger',
        'error',
        'warn',
        'message',
    ];

    /**
     * @return list<array{type: string, message: string, title?: string, duration?: int}>
     */
    public static function collect(): array
    {
        $items = [];

        foreach ((array) session()->pull('notifications', []) as $item) {
            $normalized = self::normalize($item);
            if ($normalized) {
                $items[] = $normalized;
            }
        }

        foreach (self::LEGACY_KEYS as $key) {
            $payload = session()->pull($key);
            if ($payload === null || $payload === '') {
                continue;
            }

            $normalized = self::normalize(
                is_array($payload)
                    ? array_merge(['type' => $key], $payload)
                    : ['type' => $key, 'message' => $payload]
            );

            if ($normalized) {
                $items[] = $normalized;
            }
        }

        return self::unique($items);
    }

    /**
     * @return array{type: string, message: string, title?: string, duration?: int}|null
     */
    public static function normalize(mixed $item): ?array
    {
        if (is_string($item) && $item !== '') {
            return [
                'type' => 'info',
                'message' => $item,
            ];
        }

        if (! is_array($item)) {
            return null;
        }

        $message = $item['message'] ?? null;
        if (! is_string($message) || $message === '') {
            return null;
        }

        $out = [
            'type' => self::normalizeType((string) ($item['type'] ?? 'info')),
            'message' => $message,
        ];

        if (! empty($item['title']) && is_string($item['title'])) {
            $out['title'] = $item['title'];
        }

        if (isset($item['duration']) && is_numeric($item['duration'])) {
            $out['duration'] = (int) $item['duration'];
        }

        return $out;
    }

    public static function normalizeType(string $type): string
    {
        return match ($type) {
            'success' => 'success',
            'info', 'message' => 'info',
            'warning', 'warn' => 'warning',
            'danger', 'error' => 'danger',
            default => 'info',
        };
    }

    /**
     * @param  list<array>  $items
     * @return list<array>
     */
    protected static function unique(array $items): array
    {
        $seen = [];
        $out = [];

        foreach ($items as $item) {
            $key = ($item['type'] ?? '') . '|' . ($item['message'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $item;
        }

        return $out;
    }
}
