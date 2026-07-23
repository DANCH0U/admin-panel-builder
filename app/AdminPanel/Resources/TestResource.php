<?php

namespace App\AdminPanel\Resources;

use App\AdminPanel\Engine\BaseResource;
use App\AdminPanel\Tables\Action;
use App\AdminPanel\Tables\BadgeColumn;
use App\AdminPanel\Tables\BooleanColumn;
use App\AdminPanel\Tables\BulkAction;
use App\AdminPanel\Tables\JsonColumn;
use App\AdminPanel\Tables\Search;
use App\AdminPanel\Tables\SelectFilter;
use App\AdminPanel\Tables\Tab;
use App\AdminPanel\Tables\Tabs;
use App\AdminPanel\Tables\TextColumn;
use App\Domains\Test\Models\Test;

class TestResource extends BaseResource
{
    protected string $key = 'tests';

    protected string $model = Test::class;

    public function schema(): array
    {
        return [
            'search_placeholder' => 'Search tests by name or email...',
            'search_columns' => [
                Search::column('name')->weight(3),
                Search::column('email')->weight(2),
            ],
            'tabs' => Tabs::make([
                Tab::make('all'),
                Tab::make('active')
                    ->query(fn ($q) => $q->where('status', 'active'))
                    ->color('success'),
                Tab::make('draft')
                    ->query(fn ($q) => $q->where('status', 'draft'))
                    ->color('warning'),
                Tab::make('archived')
                    ->query(fn ($q) => $q->where('status', 'archived'))
                    ->color('danger'),
                Tab::make('featured')
                    ->query(fn ($q) => $q->where('is_featured', true))
                    ->color('info'),
            ]),
            'columns' => [
                TextColumn::make('name')->label('Name')->sortable()->toggleable()->exportable(),
                TextColumn::make('email')->label('Email')->toggleable(),
                BadgeColumn::make('status')->label('Status')->colors([
                    'success' => 'active',
                    'warning' => 'draft',
                    'danger' => 'archived',
                ]),
                BadgeColumn::make('category')->label('Category')->colors([
                    'info' => 'product',
                    'success' => 'support',
                    'warning' => 'billing',
                    'default' => 'general',
                ]),
                TextColumn::make('priority')->label('Priority')->sortable(),
                BooleanColumn::make('is_featured')->label('Featured'),
                JsonColumn::make('tags')->label('Tags')->limit(3),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->sortable()
                    ->transform(fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('M d, Y') : null),
            ],
            'filters' => [
                SelectFilter::make('status')->label('Status')->options([
                    'active' => 'Active',
                    'draft' => 'Draft',
                    'archived' => 'Archived',
                ]),
                SelectFilter::make('category')->label('Category')->options([
                    'general' => 'General',
                    'billing' => 'Billing',
                    'support' => 'Support',
                    'product' => 'Product',
                ]),
            ],
            'actions' => [
                Action::make('view')
                    ->label('View')
                    ->url(fn ($record) => admin_path("tests/{$record['uuid']}")),
                Action::make('edit')
                    ->label('Edit')
                    ->url(fn ($record) => admin_path("tests/{$record['uuid']}/edit")),
                Action::make('delete')
                    ->label('Delete')
                    ->delete(fn ($record) => admin_path("tests/{$record['uuid']}")),
            ],
            'bulk_actions' => [
                BulkAction::make('feature')
                    ->label('Mark featured')
                    ->successMessage('Selected tests marked as featured.')
                    ->action(fn (array $ids) => Test::whereIn('uuid', $ids)->update(['is_featured' => true])),
                BulkAction::make('archive')
                    ->label('Archive')
                    ->type('secondary')
                    ->requiresConfirmation(
                        'Archive selected?',
                        'Selected tests will be set to archived status.',
                        'Archive',
                    )
                    ->successMessage('Selected tests archived.')
                    ->action(fn (array $ids) => Test::whereIn('uuid', $ids)->update(['status' => 'archived'])),
                BulkAction::make('delete')
                    ->label('Delete selected')
                    ->delete(fn (array $ids) => Test::whereIn('uuid', $ids)->delete()),
            ],
            'settings' => [
                'record_selection' => true,
                'selection_column' => 'uuid',
                'bulk_url' => admin_path('tests/bulk'),
            ],
        ];
    }
}
