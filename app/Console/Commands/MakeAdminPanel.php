<?php

namespace App\Console\Commands;

use App\AdminPanel\PanelRegistry;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeAdminPanel extends Command
{
    protected $signature = 'make:admin-panel
        {name : Panel key, e.g. vendor or Vendor}
        {--prefix= : URL prefix (defaults to panel key)}
        {--force : Overwrite existing menu / route files}';

    protected $description = 'Scaffold a new admin panel (menu class, routes file, provider registration snippet)';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $raw = (string) $this->argument('name');
        $key = Str::kebab(Str::snake($raw));
        $studly = Str::studly(str_replace('-', '_', $key));
        $prefix = trim((string) ($this->option('prefix') ?: $key), '/');
        $menuClass = "{$studly}Menu";
        $menuFqcn = "App\\AdminPanel\\Menus\\{$menuClass}";

        if (PanelRegistry::has($key) && ! $this->option('force')) {
            $this->components->error("Panel [{$key}] is already registered.");

            return self::FAILURE;
        }

        $this->writeMenu($menuClass, $studly, $key);
        $this->writeRoutes($key);
        $this->printProviderSnippet($key, $prefix, $menuFqcn);

        return self::SUCCESS;
    }

    protected function writeMenu(string $menuClass, string $studly, string $key): void
    {
        $path = app_path("AdminPanel/Menus/{$menuClass}.php");

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Skipped (exists): {$path}");

            return;
        }

        $stub = $this->files->get(app_path('Console/Commands/Stubs/admin-panel-menu.stub'));
        $stub = str_replace(
            ['{{ class }}', '{{ panel }}', '{{ title }}'],
            [$menuClass, $key, Str::headline($studly)],
            $stub,
        );

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $stub);
        $this->components->info("Created: {$path}");
    }

    protected function writeRoutes(string $key): void
    {
        $path = base_path("routes/panels/{$key}.php");

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Skipped (exists): {$path}");

            return;
        }

        $stub = $this->files->get(app_path('Console/Commands/Stubs/admin-panel-routes.stub'));
        $stub = str_replace(['{{ panel }}', '{{ title }}'], [$key, Str::headline($key)], $stub);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $stub);
        $this->components->info("Created: {$path}");
    }

    protected function printProviderSnippet(string $key, string $prefix, string $menuFqcn): void
    {
        $title = $this->headline($key);

        $this->newLine();
        $this->components->info('Add this registration to App\\Providers\\AdminPanelProvider::register():');
        $this->newLine();
        $this->line(<<<PHP
PanelRegistry::register('{$key}', function (Panel \$panel) {
    \$panel
        ->prefix('{$prefix}')
        ->middleware(['auth', 'panel:{$key}'])
        ->name('{$title}')
        ->logo(null)
        ->navbarTitle('{$title}')
        ->menu(\\{$menuFqcn}::class);
});
PHP);
        $this->newLine();
        $this->line("Then add routes under <fg=yellow>routes/panels/{$key}.php</>.");
    }

    protected function headline(string $key): string
    {
        return Str::headline($key).' Panel';
    }
}
