<?php

namespace App\AdminPanel\Schema\Fields;

use App\AdminPanel\Schema\Component;
use App\AdminPanel\Schema\Concerns\HasFieldOptions;

/**
 * Freeform chip tags (type + Enter / comma).
 *
 * TagsInput::make('tags')
 *     ->label('Tags')
 *     ->placeholder('Add a tag…')
 *     ->suggestions(['news', 'product'])
 *     ->max(12);
 */
class TagsInput extends Component
{
    use HasFieldOptions;

    /** @var list<string> */
    protected array $suggestions = [];

    protected ?int $max = null;

    protected bool $allowDuplicates = false;

    protected function getType(): string
    {
        return 'tags-input';
    }

    /**
     * Optional clickable suggestions shown below the input.
     *
     * @param  list<string>|array<string, string>  $suggestions
     */
    public function suggestions(array $suggestions): static
    {
        if ($suggestions !== [] && ! array_is_list($suggestions)) {
            $this->suggestions = array_values(array_unique(array_map('strval', array_keys($suggestions))));
        } else {
            $this->suggestions = array_values(array_unique(array_map('strval', $suggestions)));
        }

        return $this;
    }

    public function max(int $max): static
    {
        $this->max = max(1, $max);

        return $this;
    }

    public function allowDuplicates(bool $allow = true): static
    {
        $this->allowDuplicates = $allow;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->fieldOptions(), [
            'suggestions' => $this->suggestions,
            'max' => $this->max,
            'allowDuplicates' => $this->allowDuplicates,
        ]);
    }
}
