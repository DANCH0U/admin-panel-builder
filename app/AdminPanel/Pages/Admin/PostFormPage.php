<?php

namespace App\AdminPanel\Pages\Admin;

use App\AdminPanel\Engine\BasePage;
use App\AdminPanel\Schema\Button;
use App\AdminPanel\Schema\Card;
use App\AdminPanel\Schema\Flex;
use App\AdminPanel\Schema\Form;
use App\AdminPanel\Schema\TextInput;
use App\Models\Post;

class PostFormPage extends BasePage
{
    public function __construct(
        protected string $action,
        protected string $method = 'POST',
        protected ?string $pageTitle = null,
        protected ?Post $post = null,
    ) {}

    public function title(): ?string
    {
        return $this->pageTitle ?? ($this->post ? 'Edit Post' : 'Create Post');
    }

    public function schema(): array
    {
        return [
            Form::make()
                ->action($this->action)
                ->method($this->method)
                ->schema([
                    Card::make()
                        ->border()
                        ->label('Post')
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->required(),
                        ]),
                    Flex::make()->justify('end')->schema([
                        Button::make('Save')->submit(),
                    ]),
                ]),
        ];
    }

    public function initialData(): array
    {
        return [
            'name' => $this->post?->name ?? '',
        ];
    }
}
