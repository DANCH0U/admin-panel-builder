<?php

namespace App\AdminPanel\Resources\Admin;

use App\AdminPanel\Engine\BaseResource;
use App\Models\Post;
use App\AdminPanel\Tables\{
    Action, BadgeColumn, BooleanColumn, BulkAction, ImageColumn,
    Search, SelectFilter, Tab, Tabs, TextColumn
};
class PostResource extends BaseResource
{
    protected string $key = 'posts';

    protected string $model = Post::class;

    public function authorize(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'search_placeholder' => 'Search posts…',
            'search_columns' => [
                Search::column('title')->weight(3),
                Search::column('description')->weight(1),
            ],
            'tabs' => Tabs::make([
                Tab::make('all'),
                Tab::make('draft')
                    ->query(fn ($q) => $q->where('status', 'draft'))
                    ->color('warning'),
                Tab::make('published')
                    ->query(fn ($q) => $q->where('status', 'published'))
                    ->color('success'),
            ]),
            'columns' => [
                TextColumn::make('id')->label('ID')->sortable(),
                ImageColumn::make('image')->label('Image')->rounded(), // auto public URL
                TextColumn::make('title')->label('Title')->sortable(),
                TextColumn::make('description')->label('Description')->toggleable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),
                BooleanColumn::make('is_active')->label('Active'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->sortable()
                    ->transform(fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('M d, Y') : null),
            ],
            'filters' => [
                SelectFilter::make('is_active')
                    ->label('Active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ],
            'actions' => [
                Action::make('view')->url(fn ($r) => admin_path('posts/'.$r['id'])),
                Action::make('edit')->url(fn ($r) => admin_path('posts/'.$r['id'].'/edit')),
                Action::make('delete')->delete(fn ($r) => admin_path('posts/'.$r['id'])),
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
