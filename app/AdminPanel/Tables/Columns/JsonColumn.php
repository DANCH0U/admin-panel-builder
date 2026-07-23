<?php

namespace App\AdminPanel\Tables\Columns;

/**
 * Renders array / object / JSON values for table cells.
 *
 * Examples:
 *   JsonColumn::make('tags')->label('Tags')
 *   JsonColumn::make('metadata.title')->label('Title')  // nested / JSON path via data_get
 *   JsonColumn::make('author.roles')->using('author')   // relation + nested
 */
class JsonColumn extends AbstractColumn
{
    protected string $type = 'json';
    protected ?int $limit = 3;
    protected bool $pretty = false;

    public function limit(?int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function pretty(bool $pretty = true): static
    {
        $this->pretty = $pretty;
        return $this;
    }

    public function transformValue(mixed $value, array $record): mixed
    {
        if ($this->transformer) {
            return ($this->transformer)($value, $record);
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                return $value;
            }
        }

        if (!is_array($value)) {
            return $value;
        }

        if ($this->pretty) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // List of scalars → join (optionally limited)
        if (array_is_list($value) && collect($value)->every(fn ($item) => is_scalar($item) || $item === null)) {
            $items = array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));
            if ($this->limit !== null && count($items) > $this->limit) {
                $shown = array_slice($items, 0, $this->limit);

                return implode(', ', $shown) . ' +' . (count($items) - $this->limit);
            }

            return implode(', ', $items);
        }

        $count = count($value);

        return $count === 1
            ? (string) (array_key_first($value) ?? '1 item')
            : "{$count} items";
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'limit' => $this->limit,
            'pretty' => $this->pretty,
        ]);
    }
}
