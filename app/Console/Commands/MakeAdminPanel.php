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
        {--force : Overwrite existing panel / route files}';

    protected $description = 'Scaffold a panel class (settings + menu) under AdminPanel/Panels and register it';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $raw = (string) $this->argument('name');
        $key = Str::kebab(Str::snake($raw));
        $studly = Str::studly(str_replace(['-', '_'], ' ', $key));
        $studly = Str::beforeLast($studly, 'Panel') ?: $studly;
        $class = "{$studly}Panel";
        $fqcn = "App\\AdminPanel\\Panels\\{$class}";
        $prefix = trim((string) ($this->option('prefix') ?: $key), '/');
        $title = Str::headline($studly).' Panel';

        if (PanelRegistry::has($key) && ! $this->option('force')) {
            $this->components->error("Panel [{$key}] is already registered.");

            return self::FAILURE;
        }

        $this->writePanelClass($class, $key, $prefix, $title);
        $this->writeRoutes($key, $title);
        $this->registerInProvider($fqcn, $class);

        $this->newLine();
        $this->components->info("Panel [{$key}] ready.");
        $this->line("Class: <fg=yellow>{$fqcn}</>");
        $this->line("Routes: <fg=yellow>routes/panels/{$key}.php</>");
        $this->line('Edit menu() on the panel class to add sidebar items.');

        return self::SUCCESS;
    }

    protected function writePanelClass(string $class, string $key, string $prefix, string $title): void
    {
        $path = app_path("AdminPanel/Panels/{$class}.php");

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Skipped (exists): {$path}");

            return;
        }

        $stub = $this->files->get(app_path('Console/Commands/Stubs/admin-panel.stub'));
        $stub = str_replace(
            ['{{ class }}', '{{ id }}', '{{ prefix }}', '{{ title }}'],
            [$class, $key, $prefix, $title],
            $stub,
        );

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $stub);
        $this->components->info("Created: {$path}");
    }

    protected function writeRoutes(string $key, string $title): void
    {
        $path = base_path("routes/panels/{$key}.php");

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Skipped (exists): {$path}");

            return;
        }

        $stub = $this->files->get(app_path('Console/Commands/Stubs/admin-panel-routes.stub'));
        $stub = str_replace(['{{ panel }}', '{{ title }}'], [$key, $title], $stub);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $stub);
        $this->components->info("Created: {$path}");
    }

    protected function registerInProvider(string $fqcn, string $class): void
    {
        $path = app_path('Providers/AdminPanelProvider.php');
        $contents = $this->files->get($path);

        if (str_contains($contents, $fqcn) || str_contains($contents, "{$class}::class")) {
            $this->components->warn('Provider already lists this panel class.');

            return;
        }

        $useLine = "use {$fqcn};";
        if (! str_contains($contents, $useLine)) {
            $contents = preg_replace(
                '/(use App\\\\AdminPanel\\\\PanelRegistry;)/',
                "$1\n{$useLine}",
                $contents,
                1,
            ) ?? $contents;
        }

        if (preg_match('/protected array \$panels = \[([^\]]*)\];/s', $contents, $matches)) {
            $lines = preg_split('/\R/', $matches[1]) ?: [];
            $kept = [];

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '//')) {
                    continue;
                }
                $kept[] = rtrim($line);
            }

            $inner = implode("\n", $kept);
            if ($inner !== '' && ! str_ends_with(rtrim($inner), ',')) {
                $inner = rtrim($inner).',';
            }

            $block = $inner === ''
                ? "\n        {$class}::class,\n    "
                : "\n{$inner}\n        {$class}::class,\n    ";

            $contents = str_replace($matches[0], "protected array \$panels = [{$block}];", $contents);
            $this->files->put($path, $contents);
            $this->components->info('Registered in AdminPanelProvider::$panels.');

            return;
        }

        $this->components->warn('Could not update AdminPanelProvider automatically. Add:');
        $this->line("    {$class}::class,");
    }
}
