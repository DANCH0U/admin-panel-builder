<?php

namespace Tests\Unit;

use App\AdminPanel\Tables\Tab;
use Tests\TestCase;

class TabTest extends TestCase
{
    public function test_tab_fluent_api(): void
    {
        $tab = Tab::make('active')
            ->color('success');

        $this->assertSame('active', $tab->getValue());
        $this->assertSame('Active', $tab->getLabel());
        $this->assertSame('success', $tab->getBadgeColor());

        $array = $tab->toArray();
        $this->assertSame('active', $array['value']);
        $this->assertSame('success', $array['color']);
    }
}
