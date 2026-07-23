<?php

namespace Tests\Unit;

use App\AdminPanel\Tables\Tab;
use Tests\TestCase;

class TabTest extends TestCase
{
    public function test_tab_fluent_api(): void
    {
        $tab = Tab::make('active')
            ->color('success')
            ->hideCount();

        $this->assertSame('active', $tab->getValue());
        $this->assertSame('Active', $tab->getLabel());
        $this->assertSame('success', $tab->getBadgeColor());
        $this->assertFalse($tab->shouldShowCount());

        $counted = Tab::make('draft')->showCount();
        $this->assertTrue($counted->shouldShowCount());

        $array = $tab->toArray();
        $this->assertSame('active', $array['value']);
        $this->assertFalse($array['show_count']);
        $this->assertSame('success', $array['color']);
    }
}
