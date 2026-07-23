<?php

namespace App\AdminPanel\Engine\Search;

/**
 * Defines one searchable column with its strategy and priority weight.
 */
class SearchableColumn
{
    private string $name;
    private string $strategy = 'like';  // like | exact | fulltext | relation
    private int $weight = 1;
    private ?string $relation = null;  // for relation strategy

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function strategy(string $strategy): static
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function weight(int $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function relation(string $rel): static
    {
        $this->relation = $rel;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getRelation(): ?string
    {
        return $this->relation;
    }
}
