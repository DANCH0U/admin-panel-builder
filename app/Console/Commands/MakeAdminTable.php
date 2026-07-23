<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeAdminTable extends GeneratorCommand
{
    protected $name = 'make:admin-table';

    protected $description = 'Create an AdminPanel table resource (and optional Vue index page)';

    protected $type = 'Admin table';

    protected function getStub()
    {
        return app_path('Console/Commands/Stubs/admin-table.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\AdminPanel\Resources';
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

        if (!str_contains($model, '\\')) {
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
        $result = parent::handle();

        if ($result === false) {
            return $result;
        }

        if ($this->option('page')) {
            $this->writeIndexPage();
        }

        return self::SUCCESS;
    }

    protected function writeIndexPage(): void
    {
        $resource = Str::beforeLast(class_basename($this->qualifyClass($this->getNameInput())), 'Resource');
        $directory = resource_path('js/pages/Admin/'.Str::pluralStudly($resource));
        $path = "{$directory}/Index.vue";

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Vue page [{$path}] already exists.");

            return;
        }

        $this->files->ensureDirectoryExists($directory);

        $stubPath = app_path('Console/Commands/Stubs/admin-table-page.vue.stub');
        if (! $this->files->exists($stubPath)) {
            $stubPath = app_path('Console/Commands/Stubs/admin-table-page.stub');
        }

        $stub = $this->files->get($stubPath);
        $stub = str_replace('{{ title }}', Str::headline(Str::plural($resource)), $stub);
        $this->files->put($path, $stub);
        $this->components->info("Vue page [{$path}] created successfully.");
    }

    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The Eloquent model class basename or FQCN'],
            ['page', null, InputOption::VALUE_NONE, 'Also create an Admin Vue index page'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files'],
        ];
    }
}
