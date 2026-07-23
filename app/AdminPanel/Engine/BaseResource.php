<?php

namespace App\AdminPanel\Engine;

use App\AdminPanel\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Thin base class — resources only describe metadata.
 * All execution happens in DataGridEngine.
 */
abstract class BaseResource
{
    protected string $key = '';

    /** @var class-string<Model> */
    protected string $model = '';

    abstract public function schema(): array;

    public function authorize(): bool
    {
        return true;
    }

    public function getModel(): Model
    {
        $class = $this->model;

        if ($class === '' || !is_a($class, Model::class, true)) {
            throw new LogicException(sprintf(
                '%s::$model must be an Eloquent model class name.',
                static::class,
            ));
        }

        return new $class();
    }

    public function getKey(): string
    {
        return $this->key ?: class_basename(static::class);
    }

    /**
     * Transform raw model arrays to action-resolved rows for the frontend.
     */
    public function transform(array $items, array $actions): array
    {
        $result = [];

        foreach ($items as $item) {
            $row = $item;
            $row['table_actions'] = [];

            foreach ($actions as $action) {
                if (!$action instanceof Action) {
                    continue;
                }

                if ($resolved = $action->resolve($item)) {
                    $row['table_actions'][] = $resolved;
                }
            }

            $result[] = $row;
        }

        return $result;
    }
}
