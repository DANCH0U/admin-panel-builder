<?php

namespace App\Console\Commands\Concerns;

use App\AdminPanel\Panel;
use App\AdminPanel\PanelRegistry;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait ResolvesAdminPanelOption
{
    protected function resolvePanelOption(): Panel
    {
        $value = $this->option('panel');

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException(
                'The --panel option is required (panel id or URL prefix). Example: --panel=admin',
            );
        }

        return PanelRegistry::resolve($value);
    }

    /**
     * Studly folder/namespace segment for a panel (admin → Admin).
     */
    protected function panelStudly(Panel $panel): string
    {
        return Str::studly(str_replace(['-', '_'], ' ', $panel->getId()));
    }
}
