<?php

namespace App\AdminPanel\Pages\Admin;

use App\AdminPanel\Engine\BasePage;
use App\Models\Post;
use App\AdminPanel\Schema\{
    Button, Card, FileInput, Flex, Form, Select, Textarea, TextInput, Toggle
};

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

    public function authorize(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            Form::make()
            ->action($this->action)
            ->method($this->method)
            ->schema([
                Card::make()->border()->label('Post')->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->required(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(5),

                    FileInput::make('image')
                        ->label('Image')
                        ->image(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                        ])
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Active'),
                ]),
                Flex::make()->justify('end')->schema([
                    Button::make('Save')->submit(),
                ]),
            ]),
        ];
    }

    public function initialData(): array
    {
        $uploads = app(\App\Services\FileUploadService::class);

        return [
            'title' => $this->post?->title ?? '',
            'description' => $this->post?->description ?? '',
            'image' => $uploads->url($this->post?->image) ?? $this->post?->image ?? '',
            'image_file' => null, // required so Inertia can submit the File
            'status' => $this->post?->status ?? 'draft',
            'is_active' => (bool) ($this->post?->is_active ?? true),
        ];
    }
}
