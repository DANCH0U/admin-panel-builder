<?php

namespace App\AdminPanel\Engine\Query;

use App\AdminPanel\Engine\Contracts\QueryStageContract;

/**
 * Runs QueryStages in sequence over a QueryContext.
 * Stages are composed into a single closure chain (functional pipeline).
 */
class QueryPipeline
{
    /** @var QueryStageContract[] */
    private array $stages;

    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    public function process(QueryContext $context): QueryContext
    {
        // Build a recursive closure chain from stages (right-to-left fold)
        $pipeline = array_reduce(
            array_reverse($this->stages),
            static fn($carry, QueryStageContract $stage): \Closure =>
                static fn(QueryContext $ctx): QueryContext => $stage->handle($ctx, $carry),
            static fn(QueryContext $ctx): QueryContext => $ctx
        );

        return $pipeline($context);
    }

    /**
     * Allow dynamic stage injection (e.g. for custom per-resource stages).
     */
    public function prepend(QueryStageContract $stage): static
    {
        return new static([$stage, ...$this->stages]);
    }

    public function append(QueryStageContract $stage): static
    {
        return new static([...$this->stages, $stage]);
    }
}
