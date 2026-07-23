<?php

namespace App\AdminPanel\Engine\Query\Stages;

use App\AdminPanel\Engine\Contracts\QueryStageContract;
use App\AdminPanel\Engine\Query\QueryContext;
use Illuminate\Database\Eloquent\Relations\Relation;
use Throwable;

/**
 * Eager-loads relations from schema `relations` and column hints
 * (including dotted names like author.name → author).
 */
class RelationStage implements QueryStageContract
{
    public function handle(QueryContext $context, \Closure $next): QueryContext
    {
        $relations = $context->schema['relations'] ?? [];
        $toLoad = [];

        foreach ($relations as $relation) {
            if (is_string($relation) && str_contains($relation, ':')) {
                [$name, $cols] = explode(':', $relation, 2);
                $toLoad[$name] = fn ($q) => $q->select(explode(',', $cols));
            } elseif (is_array($relation)) {
                foreach ($relation as $name => $cols) {
                    $toLoad[$name] = fn ($q) => $q->select(explode(',', $cols));
                }
            } elseif (is_string($relation)) {
                $toLoad[$relation] = true;
            }
        }

        $columns = $context->schema['columns'] ?? [];
        $model = $context->query->getModel();

        foreach ($columns as $col) {
            if (!is_object($col) || !method_exists($col, 'getName')) {
                continue;
            }

            if (method_exists($col, 'getEagerLoad') && $col->getEagerLoad()) {
                $toLoad[$col->getEagerLoad()] = true;
                continue;
            }

            $name = $col->getName();
            if (!is_string($name) || !str_contains($name, '.')) {
                continue;
            }

            $relation = explode('.', $name, 2)[0];
            if ($this->isRelation($model, $relation)) {
                $toLoad[$relation] = true;
            }
        }

        foreach ($toLoad as $name => $constraint) {
            if ($constraint === true) {
                $context->query->with($name);
            } else {
                $context->query->with([$name => $constraint]);
            }
        }

        return $next($context);
    }

    protected function isRelation(object $model, string $name): bool
    {
        if (!method_exists($model, $name)) {
            return false;
        }

        try {
            return $model->{$name}() instanceof Relation;
        } catch (Throwable) {
            return false;
        }
    }
}
