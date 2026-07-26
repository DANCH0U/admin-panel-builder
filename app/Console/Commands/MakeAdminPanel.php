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
        {--force : Overwrite existing panel files}';

    protected $description = 'Scaffold a panel with dashboard, profile, and User CRUD by default';

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

        $replace = [
            '{{ class }}' => $class,
            '{{ id }}' => $key,
            '{{ prefix }}' => $prefix,
            '{{ title }}' => $title,
            '{{ panel }}' => $key,
            '{{ panelStudly }}' => $studly,
        ];

        $this->writeFromStub(app_path("AdminPanel/Panels/{$class}.php"), 'admin-panel.stub', $replace);
        $this->writeFromStub(base_path("routes/panels/{$key}.php"), 'admin-panel-routes.stub', $replace);

        $this->writeFromStub(
            app_path("AdminPanel/Pages/{$studly}/DashboardPage.php"),
            'admin-panel-dashboard-page.stub',
            $replace,
        );
        $this->writeFromStub(
            app_path("Http/Controllers/{$studly}/DashboardController.php"),
            'admin-panel-dashboard-controller.stub',
            $replace,
        );

        $this->writeFromStub(
            app_path("AdminPanel/Pages/{$studly}/ProfilePage.php"),
            'admin-panel-profile-page.stub',
            $replace,
        );
        $this->writeFromStub(
            app_path("Http/Controllers/{$studly}/ProfileController.php"),
            'admin-panel-profile-controller.stub',
            $replace,
        );

        $this->writeFromStub(
            app_path("AdminPanel/Resources/{$studly}/UserResource.php"),
            'admin-panel-user-resource.stub',
            $replace,
        );
        $this->writeFromStub(
            app_path("AdminPanel/Pages/{$studly}/UserFormPage.php"),
            'admin-panel-user-form.stub',
            $replace,
        );
        $this->writeFromStub(
            app_path("AdminPanel/Pages/{$studly}/UserViewPage.php"),
            'admin-panel-user-view.stub',
            $replace,
        );
        $this->writeFromStub(
            app_path("Http/Controllers/{$studly}/UserController.php"),
            'admin-panel-user-controller.stub',
            $replace,
        );

        $this->registerInProvider($fqcn, $class);

        $this->newLine();
        $this->components->info("Panel [{$key}] ready.");
        $this->line("Class: <fg=yellow>{$fqcn}</>");
        $this->line("Routes: <fg=yellow>routes/panels/{$key}.php</>");
        $this->line('Includes: Dashboard, Profile, Users CRUD (resource + form + view).');
        $this->line('Optional: add <fg=yellow>admin</> middleware on the panel for is_admin-only access.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $replace
     */
    protected function writeFromStub(string $path, string $stub, array $replace): void
    {
        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Skipped (exists): {$path}");

            return;
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $contents = $this->files->get(app_path("Console/Commands/Stubs/{$stub}"));
        $contents = str_replace(array_keys($replace), array_values($replace), $contents);
        $this->files->put($path, $contents);
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
