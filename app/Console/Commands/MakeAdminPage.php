<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ResolvesAdminPanelOption;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

class MakeAdminPage extends GeneratorCommand
{
    use ResolvesAdminPanelOption;

    protected $name = 'make:admin-page';

    protected $description = 'Create an AdminPanel schema page (render with Admin/SchemaPage)';

    protected $type = 'Admin page';

    protected ?string $panelStudly = null;

    protected function getStub()
    {
        return app_path('Console/Commands/Stubs/admin-page.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\AdminPanel\\Pages\\'.$this->panelStudly;
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

        $page = Str::beforeLast(class_basename($this->qualifyClass($this->getNameInput())), 'Page');
        $pageClass = "{$page}Page";
        $pageFqcn = "App\\AdminPanel\\Pages\\{$this->panelStudly}\\{$pageClass}";
        $slug = Str::kebab($page);
        $panelId = $panel->getId();

        $this->newLine();
        $this->components->info('Page class created. Use the generic SchemaPage renderer:');
        $this->line("    return Inertia::render('Admin/SchemaPage', (new \\{$pageFqcn}())->toInertia());");
        $this->newLine();
        $this->line("Add a route in <fg=yellow>routes/panels/{$panelId}.php</>:");
        $this->line("    Route::get('/{$slug}', [YourController::class, 'show'])->name('{$panelId}.{$slug}');");

        return self::SUCCESS;
    }

    protected function getOptions()
    {
        return [
            ['panel', null, InputOption::VALUE_REQUIRED, 'Panel id or URL prefix'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files'],
        ];
    }
}
