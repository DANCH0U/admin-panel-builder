<?php

namespace App\AdminPanel\Resources\Admin;

use App\AdminPanel\Engine\BaseResource;
use App\AdminPanel\Tables\Action;
use App\AdminPanel\Tables\BulkAction;
use App\AdminPanel\Tables\Search;
use App\AdminPanel\Tables\TextColumn;
use App\Models\Post;

class PostResource extends BaseResource
{
    protected string $key = 'posts';

    protected string $model = Post::class;

    public function schema(): array
    {
        return [
            'search_placeholder' => 'Search posts...',
            'search_columns' => [
                Search::column('name')->weight(3),
            ],
            'columns' => [
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('Name')->sortable()->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->sortable()
                    ->transform(fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('M d, Y') : null),
            ],
            'filters' => [],
            'actions' => [
                Action::make('view')
                    ->label('View')
                    ->url(fn (array $record) => admin_path('posts/'.$record['id'])),
                Action::make('edit')
                    ->label('Edit')
                    ->url(fn (array $record) => admin_path('posts/'.$record['id'].'/edit')),
                Action::make('delete')
                    ->label('Delete')
                    ->delete(fn (array $record) => admin_path('posts/'.$record['id'])),
            ],
            'bulk_actions' => [
                BulkAction::make('delete')
                    ->label('Delete selected')
                    ->delete(fn (array $ids) => Post::whereIn('id', $ids)->delete()),
            ],
            'settings' => [
                'record_selection' => true,
                'selection_column' => 'id',
                'bulk_url' => admin_path('posts/bulk'),
            ],
        ];
    }
}
