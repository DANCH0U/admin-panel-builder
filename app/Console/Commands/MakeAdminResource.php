<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ResolvesAdminPanelOption;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MakeAdminResource extends Command
{
    use ResolvesAdminPanelOption;

    protected $signature = 'make:admin-resource
        {name : Resource name, e.g. Post or PostResource}
        {--panel= : Panel id or URL prefix (required)}
        {--model= : Eloquent model basename or FQCN}
        {--form : Generate form page + create/edit controller methods}
        {--view : Generate view page + show controller method}
        {--force : Overwrite existing files}';

    protected $description = 'Generate an AdminPanel resource, controller, and optional form/view pages';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $panel = $this->resolvePanelOption();
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $raw = $this->argument('name');
        $resource = Str::studly(Str::beforeLast(Str::studly($raw), 'Resource') ?: Str::studly($raw));
        $resourceClass = "{$resource}Resource";
        $modelOption = $this->option('model');
        $modelClass = $modelOption
            ? Str::studly(class_basename($modelOption))
            : $resource;
        $modelFqcn = $modelOption && str_contains($modelOption, '\\')
            ? ltrim($modelOption, '\\')
            : "App\\Models\\{$modelClass}";
        $modelVar = lcfirst($modelClass);
        $key = Str::plural(Str::kebab($modelClass));
        $title = Str::headline(Str::plural($resource));
        $titleSingular = Str::headline($resource);
        $titleLower = Str::lower($title);
        $withForm = (bool) $this->option('form');
        $withView = (bool) $this->option('view');
        $panelId = $panel->getId();
        $panelStudly = $this->panelStudly($panel);

        $resourceNs = "App\\AdminPanel\\Resources\\{$panelStudly}";
        $pagesNs = "App\\AdminPanel\\Pages\\{$panelStudly}";
        $controllerNs = "App\\Http\\Controllers\\{$panelStudly}";

        $replacements = [
            '{{ namespace }}' => $resourceNs,
            '{{ pagesNamespace }}' => $pagesNs,
            '{{ controllerNamespace }}' => $panelStudly,
            '{{ resourceFqcn }}' => "{$resourceNs}\\{$resourceClass}",
            '{{ class }}' => $resourceClass,
            '{{ resourceClass }}' => $resourceClass,
            '{{ modelFqcn }}' => $modelFqcn,
            '{{ modelClass }}' => $modelClass,
            '{{ modelVar }}' => $modelVar,
            '{{ key }}' => $key,
            '{{ title }}' => $title,
            '{{ titleSingular }}' => $titleSingular,
            '{{ titleLower }}' => $titleLower,
            '{{ createUrl }}' => $withForm ? "admin_path('{$key}/create')" : 'null',
            '{{ createLabel }}' => $withForm ? "'Add {$titleSingular}'" : 'null',
        ];

        $this->writeResource($panelStudly, $resourceClass, $replacements, $withForm, $withView);
        $this->writeController($panelStudly, $resource, $modelClass, $replacements, $withForm, $withView, $pagesNs);

        if ($withForm) {
            $this->writeFromStub(
                app_path("AdminPanel/Pages/{$panelStudly}/{$resource}FormPage.php"),
                'admin-form-page.stub',
                array_merge($replacements, [
                    '{{ namespace }}' => $pagesNs,
                    '{{ class }}' => "{$resource}FormPage",
                ]),
            );
        }

        if ($withView) {
            $this->writeFromStub(
                app_path("AdminPanel/Pages/{$panelStudly}/{$resource}ViewPage.php"),
                'admin-view-page.stub',
                array_merge($replacements, [
                    '{{ namespace }}' => $pagesNs,
                    '{{ class }}' => "{$resource}ViewPage",
                ]),
            );
        }

        $this->newLine();
        $this->components->info('Admin resource scaffolded.');
        $this->warn('Note: this command does not generate models or migrations — create those separately.');
        $this->line("Add routes to <fg=yellow>routes/panels/{$panelId}.php</>:");
        $this->newLine();
        $this->line("    Route::post('/{$key}/bulk', [\\{$controllerNs}\\{$resource}Controller::class, 'bulk'])");
        $this->line("        ->name('{$key}.bulk');");
        $this->line("    Route::resource('{$key}', \\{$controllerNs}\\{$resource}Controller::class)" .
            ($withForm || $withView ? ';' : "->only(['index', 'destroy']);"));
        $this->newLine();
        $menuHint = $panel->getMenu() ?? 'your panel menu class';
        $this->line("Optional menu item in <fg=yellow>{$menuHint}</>:");
        $this->line("    MenuItem::link('{$key}', admin_path('{$key}', '{$panelId}'))->icon('heroicons:rectangle-stack')->title('{$title}'),");

        return self::SUCCESS;
    }

    protected function writeResource(
        string $panelStudly,
        string $resourceClass,
        array $base,
        bool $withForm,
        bool $withView,
    ): void {
        $actions = [];

        if ($withView) {
            $actions[] = <<<'PHP'
                Action::make('view')
                    ->label('View')
                    ->url(fn (array $record) => admin_path('{{ key }}/'.$record['id'])),
PHP;
        }

        if ($withForm) {
            $actions[] = <<<'PHP'
                Action::make('edit')
                    ->label('Edit')
                    ->url(fn (array $record) => admin_path('{{ key }}/'.$record['id'].'/edit')),
PHP;
        }

        $actions[] = <<<'PHP'
                Action::make('delete')
                    ->label('Delete')
                    ->delete(fn (array $record) => admin_path('{{ key }}/'.$record['id'])),
PHP;

        $this->writeFromStub(
            app_path("AdminPanel/Resources/{$panelStudly}/{$resourceClass}.php"),
            'admin-resource.stub',
            array_merge($base, [
                '{{ actions }}' => implode("\n", $actions),
            ]),
        );
    }

    protected function writeController(
        string $panelStudly,
        string $resource,
        string $modelClass,
        array $base,
        bool $withForm,
        bool $withView,
        string $pagesNs,
    ): void {
        $formImport = $withForm
            ? "use {$pagesNs}\\{$resource}FormPage;\n"
            : '';
        $viewImport = $withView
            ? "use {$pagesNs}\\{$resource}ViewPage;\n"
            : '';

        $formMethods = '';
        if ($withForm) {
            $formMethods = <<<PHP

    public function create()
    {
        \$page = new {$resource}FormPage(
            action: admin_path('{$base['{{ key }}']}'),
            method: 'POST',
            pageTitle: 'Create {$base['{{ titleSingular }}']}',
        );

        return Inertia::render('Admin/SchemaPage', \$page->toInertia([
            'initialData' => \$page->initialData(),
        ]));
    }

    public function store(Request \$request)
    {
        \$validated = \$request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        {$modelClass}::create(\$validated);
        Notify::success('{$base['{{ titleSingular }}']} created.');

        return redirect()->route('{$base['{{ key }}']}.index');
    }

    public function edit({$modelClass} \${$base['{{ modelVar }}']})
    {
        \$page = new {$resource}FormPage(
            action: admin_path('{$base['{{ key }}']}/'.\${$base['{{ modelVar }}']}->getKey()),
            method: 'PUT',
            pageTitle: 'Edit {$base['{{ titleSingular }}']}',
            {$base['{{ modelVar }}']}: \${$base['{{ modelVar }}']},
        );

        return Inertia::render('Admin/SchemaPage', \$page->toInertia([
            'initialData' => \$page->initialData(),
        ]));
    }

    public function update(Request \$request, {$modelClass} \${$base['{{ modelVar }}']})
    {
        \$validated = \$request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        \${$base['{{ modelVar }}']}->update(\$validated);
        Notify::success('{$base['{{ titleSingular }}']} updated.');

        return redirect()->route('{$base['{{ key }}']}.index');
    }

    public function destroy({$modelClass} \${$base['{{ modelVar }}']})
    {
        \${$base['{{ modelVar }}']}->delete();
        Notify::success('{$base['{{ titleSingular }}']} deleted.');

        return back();
    }
PHP;
        } else {
            $formMethods = <<<PHP

    public function destroy({$modelClass} \${$base['{{ modelVar }}']})
    {
        \${$base['{{ modelVar }}']}->delete();
        Notify::success('{$base['{{ titleSingular }}']} deleted.');

        return back();
    }
PHP;
        }

        $viewMethods = '';
        if ($withView) {
            $viewMethods = <<<PHP

    public function show({$modelClass} \${$base['{{ modelVar }}']})
    {
        \$page = new {$resource}ViewPage(\${$base['{{ modelVar }}']});

        return Inertia::render('Admin/SchemaPage', \$page->toInertia());
    }
PHP;
        }

        $this->writeFromStub(
            app_path("Http/Controllers/{$panelStudly}/{$resource}Controller.php"),
            'admin-controller.stub',
            array_merge($base, [
                '{{ class }}' => "{$resource}Controller",
                '{{ formImport }}' => $formImport,
                '{{ viewImport }}' => $viewImport,
                '{{ formMethods }}' => $formMethods,
                '{{ viewMethods }}' => $viewMethods,
            ]),
        );
    }

    protected function writeFromStub(string $path, string $stub, array $replace): void
    {
        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Skipped (exists): {$path}");

            return;
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $contents = $this->files->get(app_path("Console/Commands/Stubs/{$stub}"));
        $contents = str_replace(array_keys($replace), array_values($replace), $contents);
        $contents = str_replace(array_keys($replace), array_values($replace), $contents);
        $this->files->put($path, $contents);
        $this->components->info("Created: {$path}");
    }
}
