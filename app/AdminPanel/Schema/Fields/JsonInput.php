<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasFieldOptions;
use App\AdminPanel\Schema\Concerns\HasSchema;

/**
 * Repeater of schema blocks stored as a JSON array of objects.
 *
 * JsonInput::make('blocks')
 *     ->schema([
 *         TextInput::make('title')->label('Title'),
 *         FileInput::make('image')->image(),
 *     ])
 *     ->addable()
 *     ->deletable()
 *     ->reorderable()
 *     ->minItems(1)
 *     ->maxItems(10);
 */
class JsonInput extends Component
{
    use HasFieldOptions;
    use HasSchema;

    protected bool $addable = true;

    protected bool $deletable = true;

    protected bool $reorderable = false;

    protected bool $collapsible = false;

    protected bool $collapsed = false;

    protected int $minItems = 0;

    protected ?int $maxItems = null;

    protected int $defaultItems = 1;

    protected string $addLabel = 'Add item';

    protected string $itemLabel = 'Item';

    protected function getType(): string
    {
        return 'json-input';
    }

    public function addable(bool $addable = true): static
    {
        $this->addable = $addable;

        return $this;
    }

    public function deletable(bool $deletable = true): static
    {
        $this->deletable = $deletable;

        return $this;
    }

    public function reorderable(bool $reorderable = true): static
    {
        $this->reorderable = $reorderable;

        return $this;
    }

    public function collapsible(bool $collapsible = true): static
    {
        $this->collapsible = $collapsible;

        return $this;
    }

    public function collapsed(bool $collapsed = true): static
    {
        $this->collapsed = $collapsed;
        $this->collapsible = true;

        return $this;
    }

    public function minItems(int $min): static
    {
        $this->minItems = max(0, $min);

        return $this;
    }

    public function maxItems(int $max): static
    {
        $this->maxItems = max(1, $max);

        return $this;
    }

    public function defaultItems(int $count): static
    {
        $this->defaultItems = max(0, $count);

        return $this;
    }

    public function addLabel(string $label): static
    {
        $this->addLabel = $label;

        return $this;
    }

    public function itemLabel(string $label): static
    {
        $this->itemLabel = $label;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->fieldOptions(), [
            'schema' => $this->serializeSchema(),
            'addable' => $this->addable,
            'deletable' => $this->deletable,
            'reorderable' => $this->reorderable,
            'collapsible' => $this->collapsible,
            'collapsed' => $this->collapsed,
            'minItems' => $this->minItems,
            'maxItems' => $this->maxItems,
            'defaultItems' => $this->defaultItems,
            'addLabel' => $this->addLabel,
            'itemLabel' => $this->itemLabel,
        ]);
    }
}
