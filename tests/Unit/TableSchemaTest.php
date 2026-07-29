<?php

namespace Tests\Unit;

use App\AdminPanel\Tables\BooleanColumn;
use App\AdminPanel\Tables\Tab;
use App\AdminPanel\Tables\Tabs;
use App\AdminPanel\Tables\TextColumn;
use Tests\TestCase;

class TableSchemaTest extends TestCase
{
    public function test_text_column_exposes_max_length(): void
    {
        $column = TextColumn::make('description')->maxLength(10)->toArray();

        $this->assertSame(10, $column['max_length']);
    }

    public function test_boolean_column_exposes_labels(): void
    {
        $column = BooleanColumn::make('is_active')->labels('On', 'Off')->toArray();

        $this->assertSame('On', $column['true_label']);
        $this->assertSame('Off', $column['false_label']);
    }

    public function test_hidden_tabs_are_dropped_without_leaving_gaps(): void
    {
        $tabs = Tabs::make([
            Tab::make('all'),
            Tab::make('trashed')->hidden(),
            Tab::make('published'),
        ])->toArray();

        $this->assertSame([0, 1], array_keys($tabs));
        $this->assertSame(['all', 'published'], array_column($tabs, 'value'));
    }
}
