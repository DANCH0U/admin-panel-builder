<?php

namespace App\AdminPanel\Pages;

use App\AdminPanel\Engine\BasePage;
use App\AdminPanel\Schema\Button;
use App\AdminPanel\Schema\Card;
use App\AdminPanel\Schema\Checkbox;
use App\AdminPanel\Schema\DateTimeInput;
use App\AdminPanel\Schema\FileInput;
use App\AdminPanel\Schema\Flex;
use App\AdminPanel\Schema\Form;
use App\AdminPanel\Schema\Grid;
use App\AdminPanel\Schema\Heading;
use App\AdminPanel\Schema\JsonInput;
use App\AdminPanel\Schema\ListInput;
use App\AdminPanel\Schema\MultiSelect;
use App\AdminPanel\Schema\NumberInput;
use App\AdminPanel\Schema\Select;
use App\AdminPanel\Schema\Tab;
use App\AdminPanel\Schema\Tabs;
use App\AdminPanel\Schema\Text;
use App\AdminPanel\Schema\Textarea;
use App\AdminPanel\Schema\TextInput;
use App\AdminPanel\Schema\Toggle;
use App\Domains\Test\Models\Test;

class TestFormPage extends BasePage
{
    public function __construct(
        protected string $action,
        protected string $method = 'POST',
        protected ?string $pageTitle = null,
        protected ?Test $test = null,
    ) {}

    public function title(): ?string
    {
        return $this->pageTitle ?? ($this->test ? 'Edit test' : 'Create test');
    }

    public function schema(): array
    {
        return [
            Form::make()
                ->action($this->action)
                ->method($this->method)
                ->schema([
                    Heading::make('Schema feature tour')->level(2),
                    Text::make('Every field is driven from PHP with fluent -> methods and rendered with shadcn/vue.')
                        ->variant('subdued'),

                    Tabs::make()->schema([
                        Tab::make('basics')->label('Basics')->schema([
                            Card::make()->border()
                                ->label('Identity')
                                ->helpText('Text, email, select, and textarea.')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required()
                                            ->placeholder('Launch checklist')
                                            ->hint('Shown in the table name column.'),
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->placeholder('name@example.com'),
                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'active' => 'Active',
                                                'draft' => 'Draft',
                                                'archived' => 'Archived',
                                            ])
                                            ->required(),
                                        Select::make('category')
                                            ->label('Category')
                                            ->options([
                                                'general' => 'General',
                                                'billing' => 'Billing',
                                                'support' => 'Support',
                                                'product' => 'Product',
                                            ]),
                                    ]),
                                    Textarea::make('description')
                                        ->label('Description')
                                        ->placeholder('Short summary…')
                                        ->rows(4),
                                ]),
                        ]),

                        Tab::make('details')->label('Details')->schema([
                            Card::make()->border()
                                ->label('Numbers, datetime, tags')
                                ->schema([
                                    Grid::make(3)->schema([
                                        NumberInput::make('priority')
                                            ->label('Priority')
                                            ->min(1)
                                            ->max(5)
                                            ->step(1)
                                            ->controls()
                                            ->hint('1 = low, 5 = critical'),
                                        NumberInput::make('score')
                                            ->label('Score')
                                            ->min(0)
                                            ->max(100)
                                            ->step(1)
                                            ->controls(),
                                        DateTimeInput::make('published_at')
                                            ->label('Published at')
                                            ->withTime()
                                            ->placeholder('Pick date & time'),
                                    ]),
                                    MultiSelect::make('tags')
                                        ->label('Tags')
                                        ->options([
                                            'alpha' => 'Alpha',
                                            'beta' => 'Beta',
                                            'urgent' => 'Urgent',
                                            'internal' => 'Internal',
                                            'demo' => 'Demo',
                                        ])
                                        ->placeholder('Select tags'),
                                    Textarea::make('notes')->label('Internal notes'),
                                ]),
                        ]),

                        Tab::make('media')->label('Media & list')->schema([
                            Card::make()->border()
                                ->label('Cover image')
                                ->helpText('FileInput::make(...)->image() enables image accept + live preview.')
                                ->schema([
                                    FileInput::make('cover_image')
                                        ->label('Cover image')
                                        ->image()
                                        ->directory('tests/covers')
                                        ->maxSizeKb(2048)
                                        ->hint('PNG, JPG, WEBP up to 2MB.'),
                                ]),
                            Card::make()->border()
                                ->label('Checklist (list input)')
                                ->helpText('ListInput builds a dynamic string list from PHP.')
                                ->schema([
                                    ListInput::make('checklist')
                                        ->label('Checklist items')
                                        ->placeholder('Add a step…')
                                        ->addLabel('Add step')
                                        ->min(0)
                                        ->max(20),
                                ]),
                        ]),

                        Tab::make('flags')->label('Flags')->schema([
                            Card::make()->border()
                                ->label('Visibility flags')
                                ->helpText('Toggle + checkbox with visibleWhen().')
                                ->schema([
                                    Toggle::make('is_featured')
                                        ->label('Featured')
                                        ->helpText('Highlight this record in lists.'),
                                    Toggle::make('is_public')->label('Public'),
                                    Checkbox::make('confirm_review')
                                        ->label('Mark as reviewed')
                                        ->visibleWhen('is_featured', true),
                                    Text::make('Review checkbox appears when Featured is on.')
                                        ->variant('caption')
                                        ->visibleWhen('is_featured', true),
                                ]),
                        ]),

                        Tab::make('json')->label('JSON blocks')->schema([
                            Card::make()->border()
                                ->label('Metadata blocks')
                                ->helpText('JsonInput::make(...)->schema([...])->addable()->deletable()->reorderable()')
                                ->schema([
                                    JsonInput::make('metadata')
                                        ->label('Content blocks')
                                        ->itemLabel('Block')
                                        ->addLabel('Add block')
                                        ->addable()
                                        ->deletable()
                                        ->reorderable()
                                        ->collapsible()
                                        ->minItems(0)
                                        ->maxItems(8)
                                        ->defaultItems(1)
                                        ->hint('Each block is a schema row stored in a JSON array.')
                                        ->schema([
                                            TextInput::make('title')
                                                ->label('Title')
                                                ->required()
                                                ->placeholder('Block title'),
                                            Select::make('kind')
                                                ->label('Kind')
                                                ->options([
                                                    'note' => 'Note',
                                                    'asset' => 'Asset',
                                                    'link' => 'Link',
                                                ]),
                                            Textarea::make('body')
                                                ->label('Body')
                                                ->placeholder('Optional details…')
                                                ->rows(3),
                                            FileInput::make('image')
                                                ->label('Image')
                                                ->image()
                                                ->directory('tests/blocks')
                                                ->maxSizeKb(2048),
                                            Toggle::make('enabled')->label('Enabled'),
                                        ]),
                                ]),
                        ]),
                    ]),

                    Flex::make()->justify('end')->schema([
                        Button::make('Cancel')
                            ->type('button')
                            ->variant('outline')
                            ->url(admin_path('tests')),
                        Button::make($this->test ? 'Save changes' : 'Create test')->submit(),
                    ]),
                ]),
        ];
    }

    public function initialData(): array
    {
        if (!$this->test) {
            return [
                'name' => '',
                'email' => '',
                'description' => '',
                'status' => 'draft',
                'category' => 'general',
                'priority' => 1,
                'score' => 0,
                'is_featured' => false,
                'is_public' => true,
                'tags' => [],
                'metadata' => [
                    [
                        'title' => 'Intro',
                        'kind' => 'note',
                        'body' => 'First structured JSON block.',
                        'image' => '/admin-logo.svg',
                        'enabled' => true,
                    ],
                ],
                'published_at' => '',
                'notes' => '',
                'cover_image' => '',
                'checklist' => ['Define schema', 'Wire DataTable', 'Seed sample data'],
            ];
        }

        $test = $this->test;

        return [
            'name' => $test->name,
            'email' => $test->email,
            'description' => $test->description,
            'status' => $test->status,
            'category' => $test->category,
            'priority' => $test->priority,
            'score' => $test->score,
            'is_featured' => $test->is_featured,
            'is_public' => $test->is_public,
            'tags' => $test->tags ?? [],
            'metadata' => $this->normalizeMetadataBlocks($test->metadata),
            'published_at' => $test->published_at?->format('Y-m-d\\TH:i'),
            'notes' => $test->notes,
            'cover_image' => $test->cover_image,
            'checklist' => $test->checklist ?? [],
        ];
    }

    /**
     * @param  mixed  $metadata
     * @return array<int, array<string, mixed>>
     */
    private function normalizeMetadataBlocks(mixed $metadata): array
    {
        if (!is_array($metadata) || $metadata === []) {
            return [[
                'title' => '',
                'kind' => 'note',
                'body' => '',
                'image' => '',
                'enabled' => true,
            ]];
        }

        // Legacy flat object → wrap as one block
        if (!array_is_list($metadata)) {
            return [[
                'title' => (string) ($metadata['source'] ?? 'Imported'),
                'kind' => 'note',
                'body' => json_encode($metadata, JSON_UNESCAPED_SLASHES),
                'image' => '',
                'enabled' => true,
            ]];
        }

        return array_values(array_map(function ($row) {
            $row = is_array($row) ? $row : [];

            return [
                'title' => $row['title'] ?? '',
                'kind' => $row['kind'] ?? 'note',
                'body' => $row['body'] ?? '',
                'image' => $row['image'] ?? '',
                'enabled' => (bool) ($row['enabled'] ?? true),
            ];
        }, $metadata));
    }
}
