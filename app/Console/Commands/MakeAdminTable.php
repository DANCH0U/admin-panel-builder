<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ResolvesAdminPanelOption;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

class MakeAdminTable extends GeneratorCommand
{
    use ResolvesAdminPanelOption;

    protected $name = 'make:admin-table';

    protected $description = 'Create an AdminPanel table resource (renders via Admin/ResourceIndex)';

    protected $type = 'Admin table';

    protected ?string $panelStudly = null;

    protected function getStub()
    {
        return app_path('Console/Commands/Stubs/admin-table.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\AdminPanel\\Resources\\'.$this->panelStudly;
    }

    protected function qualifyClass($name)
    {
        $name = Str::endsWith($name, 'Resource') ? $name : "{$name}Resource";

        return parent::qualifyClass($name);
    }

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $class = class_basename($name);
        $resource = Str::beforeLast($class, 'Resource');
        $modelOption = $this->option('model');
        $model = ltrim($modelOption ?: "App\\Models\\{$resource}", '\\');

        if (! str_contains($model, '\\')) {
            $model = "App\\Models\\{$model}";
        }

        $route = Str::plural(Str::kebab(class_basename($model)));

        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}', '{{ modelVar }}', '{{ route }}', '{{ key }}'],
            [
                $this->getNamespace($name),
                $class,
                '\\'.$model,
                lcfirst(class_basename($model)),
                $route,
                $route,
            ],
            $stub,
        );
    }

    public function handle()
    {
        try {
            $panel = $this->resolvePanelOption();
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->panelStudly = $this->panelStudly($panel);

        $result = parent::handle();

        if ($result === false) {
            return $result;
        }

        $class = class_basename($this->qualifyClass($this->getNameInput()));
        $resource = Str::beforeLast($class, 'Resource');
        $key = Str::plural(Str::kebab($resource));
        $panelId = $panel->getId();
        $title = Str::headline(Str::plural($resource));
        $controllerFqcn = "App\\Http\\Controllers\\{$this->panelStudly}\\{$resource}Controller";

        $this->newLine();
        $this->line("Render index with <fg=yellow>Admin/ResourceIndex</> and add routes to <fg=yellow>routes/panels/{$panelId}.php</>:");
        $this->line("    Route::resource('{$key}', \\{$controllerFqcn}::class)->only(['index', 'destroy']);");
        $menuHint = $panel::class;
        $this->line("Optional menu item in <fg=yellow>{$menuHint}::menu()</>:");
        $this->line("    MenuItem::link('{$key}', admin_path('{$key}', '{$panelId}'))->icon('heroicons:rectangle-stack')->title('{$title}'),");

        return self::SUCCESS;
    }

    protected function getOptions()
    {
        return [
            ['panel', null, InputOption::VALUE_REQUIRED, 'Panel id or URL prefix'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The Eloquent model class basename or FQCN'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files'],
        ];
    }
}
