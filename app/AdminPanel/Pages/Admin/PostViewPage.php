<?php

namespace App\AdminPanel\Pages\Admin;

use App\AdminPanel\Engine\BasePage;
use App\Models\Post;
use App\AdminPanel\Schema\{Button, Card, Flex, Heading, Image, KeyValue, Text};

class PostViewPage extends BasePage
{
    public function __construct(protected Post $post) {}

    public function title(): ?string
    {
        return $this->post->title ?? 'Post';
    }

    public function authorize(): bool
    {
        return true;
    }

    public function schema(): array
    {
        $record = $this->post;

        return [
            Flex::make()->justify('between')->schema([
                Heading::make($record->title ?? 'Post')->level(2),
                Flex::make()->gap(2)->schema([
                    Button::make('Edit')
                        ->variant('outline')
                        ->url(admin_path('posts/'.$record->getKey().'/edit')),
                    Button::make('Back')
                        ->variant('secondary')
                        ->url(admin_path('posts')),
                ]),
            ]),
            Card::make()->border()->schema([
                Text::make($record->description ?? '')->variant('body'),
                KeyValue::make()->entries([
                    'Title' => $record->title,
                    'Description' => $record->description,
                    'Status' => $record->status,
                    'Active' => $record->is_active ? 'Yes' : 'No',
                    'Date' => $record->created_at?->format('M d, Y'),
                ]),
            ]),
            Image::make($record->image)->label('Image'),
        ];
    }
}
