<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeAdminPage extends GeneratorCommand
{
    protected $name = 'make:admin-page';

    protected $description = 'Create an AdminPanel schema page and Vue renderer';

    protected $type = 'Admin page';

    protected function getStub()
    {
        return app_path('Console/Commands/Stubs/admin-page.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\AdminPanel\Pages';
    }

    protected function qualifyClass($name)
    {
        $name = Str::endsWith($name, 'Page') ? $name : "{$name}Page";

        return parent::qualifyClass($name);
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);
        $title = Str::headline(Str::beforeLast(class_basename($name), 'Page'));

        return str_replace('{{ title }}', $title, $stub);
    }

    public function handle()
    {
        $result = parent::handle();

        if ($result === false) {
            return $result;
        }

        $this->writeVuePage();

        return self::SUCCESS;
    }

    protected function writeVuePage(): void
    {
        $page = Str::beforeLast(class_basename($this->qualifyClass($this->getNameInput())), 'Page');
        $directory = resource_path('js/pages/Admin');
        $path = "{$directory}/{$page}.vue";

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->warn("Vue page [{$path}] already exists.");

            return;
        }

        $this->files->ensureDirectoryExists($directory);

        $stubPath = app_path('Console/Commands/Stubs/admin-page.vue.stub');
        if (! $this->files->exists($stubPath)) {
            $stubPath = app_path('Console/Commands/Stubs/admin-page-vue.stub');
        }

        $stub = $this->files->get($stubPath);
        $stub = str_replace('{{ title }}', Str::headline($page), $stub);
        $this->files->put($path, $stub);
        $this->components->info("Vue page [{$path}] created successfully.");
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files'],
        ];
    }
}
