<?php

namespace App\AdminPanel\Schema\UI;

use App\AdminPanel\Schema\Component;

/**
 * Read-only key/value (or nested JSON) display for view pages.
 *
 * KeyValue::make()
 *     ->label('Details')
 *     ->entries(['Status' => 'active', 'Email' => 'a@b.com']);
 *
 * KeyValue::make()
 *     ->label('Metadata')
 *     ->entries($model->metadata); // nested arrays render as JSON
 */
class KeyValue extends Component
{
    protected array $entries = [];

    protected function getType(): string
    {
        return 'key-value';
    }

    public static function make(mixed $name = null): static
    {
        return parent::make($name);
    }

    public function entries(array $entries): static
    {
        $this->entries = $entries;
        return $this;
    }

    public function toArray(): array
    {
        $normalized = [];

        foreach ($this->entries as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $normalized[] = [
                    'key' => (string) $key,
                    'value' => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'json' => true,
                ];
            } else {
                $normalized[] = [
                    'key' => (string) $key,
                    'value' => $value === null || $value === '' ? '—' : (string) $value,
                    'json' => false,
                ];
            }
        }

        return array_merge(parent::toArray(), [
            'entries' => $normalized,
        ]);
    }
}
