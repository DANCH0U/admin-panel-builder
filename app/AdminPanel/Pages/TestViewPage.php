<?php

namespace App\AdminPanel\Pages;

use App\AdminPanel\Engine\BasePage;
use App\AdminPanel\Schema\Button;
use App\AdminPanel\Schema\Card;
use App\AdminPanel\Schema\Flex;
use App\AdminPanel\Schema\Grid;
use App\AdminPanel\Schema\Heading;
use App\AdminPanel\Schema\KeyValue;
use App\AdminPanel\Schema\Section;
use App\AdminPanel\Schema\Text;
use App\Domains\Test\Models\Test;

class TestViewPage extends BasePage
{
    public function __construct(protected Test $test) {}

    public function title(): ?string
    {
        return $this->test->name;
    }

    public function schema(): array
    {
        $test = $this->test;

        $metadataEntries = [];
        $blocks = is_array($test->metadata)
            ? (array_is_list($test->metadata) ? $test->metadata : [$test->metadata])
            : [];

        foreach ($blocks as $i => $block) {
            $metadataEntries['Block ' . ($i + 1)] = $block;
        }

        return [
            Flex::make()->justify('between')->schema([
                Heading::make($test->name)->level(2),
                Flex::make()->schema([
                    Button::make('Edit')
                        ->variant('outline')
                        ->url(admin_path("tests/{$test->uuid}/edit")),
                    Button::make('Back to list')
                        ->variant('secondary')
                        ->url(admin_path('tests')),
                ]),
            ]),

            Grid::make(2)->schema([
                Card::make()->border()->label('Basics')->schema([
                    KeyValue::make()->entries([
                        'Status' => $test->status,
                        'Category' => $test->category ?: '—',
                        'Email' => $test->email ?: '—',
                        'Description' => $test->description ?: '—',
                    ]),
                ]),
                Card::make()->border()->label('Details')->schema([
                    KeyValue::make()->entries([
                        'Priority' => $test->priority,
                        'Score' => $test->score ?? '—',
                        'Featured' => $test->is_featured ? 'Yes' : 'No',
                        'Public' => $test->is_public ? 'Yes' : 'No',
                        'Tags' => empty($test->tags) ? '—' : implode(', ', $test->tags),
                        'Published' => $test->published_at?->format('M d, Y H:i') ?: '—',
                    ]),
                ]),
            ]),

            Section::make('json')->label('Metadata blocks')->schema([
                empty($metadataEntries)
                    ? Text::make('No blocks.')->variant('subdued')
                    : KeyValue::make()->entries($metadataEntries),
            ]),

            Section::make('notes')->label('Notes')->schema([
                Text::make($test->notes ?: 'No notes.')->variant('subdued'),
            ]),

            Section::make('checklist')->label('Checklist')->schema([
                KeyValue::make()->entries(
                    empty($test->checklist)
                        ? ['Items' => '—']
                        : collect($test->checklist)
                            ->mapWithKeys(fn ($item, $i) => ['#' . ($i + 1) => $item])
                            ->all()
                ),
            ]),

            Section::make('cover')->label('Cover')->schema([
                Text::make($test->cover_image ?: 'No cover image.')->variant('subdued'),
            ]),
        ];
    }
}
