<?php

namespace App\AdminPanel\Pages\Admin;

use App\AdminPanel\Engine\BasePage;
use App\AdminPanel\Schema\Button;
use App\AdminPanel\Schema\Card;
use App\AdminPanel\Schema\Flex;
use App\AdminPanel\Schema\Heading;
use App\AdminPanel\Schema\KeyValue;
use App\Models\Post;

class PostViewPage extends BasePage
{
    public function __construct(protected Post $post) {}

    public function title(): ?string
    {
        return $this->post->name ?? 'Post';
    }

    public function schema(): array
    {
        $record = $this->post;

        return [
            Flex::make()->justify('between')->schema([
                Heading::make($record->name ?? 'Post')->level(2),
                Flex::make()->schema([
                    Button::make('Edit')
                        ->variant('outline')
                        ->url(admin_path('posts/'.$record->getKey().'/edit')),
                    Button::make('Back')
                        ->variant('secondary')
                        ->url(admin_path('posts')),
                ]),
            ]),
            Card::make()->border()->label('Details')->schema([
                KeyValue::make()->entries([
                    'ID' => $record->getKey(),
                    'Name' => $record->name ?? '—',
                    'Created' => $record->created_at?->format('M d, Y H:i') ?? '—',
                ]),
            ]),
        ];
    }
}
