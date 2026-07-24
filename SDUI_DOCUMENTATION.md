# AdminPanel PHP Schema API

Build admin forms, pages, and data tables in PHP. Laravel serializes the schema, Inertia sends it as props, and the existing Vue renderers provide the interface.

Application code should use the public `App\AdminPanel\Schema` and `App\AdminPanel\Tables` namespaces. Classes under `Engine` are implementation details, except for the `BasePage`, `BaseResource`, and `DataGridEngine` entry points.

## Generate a page or table

```bash
php artisan make:admin-page Reports --panel=admin
php artisan make:admin-table Product --panel=admin --model=App\\Models\\Product
php artisan make:admin-resource Post --panel=admin --form --view
```

`make:admin-page` creates `app/AdminPanel/Pages/ReportsPage.php`. Render it with the generic `Admin/SchemaPage` Inertia page.

`make:admin-table` / `make:admin-resource` create resources (and controllers for resource). Table indexes use generic `Admin/ResourceIndex` — no per-resource Vue file.

All three commands require `--panel=` (registry id or URL prefix).
## Forms and pages

Create a page by extending `BasePage` and returning public schema components:

```php
<?php

namespace App\AdminPanel\Pages;

use App\AdminPanel\Engine\BasePage;
use App\AdminPanel\Schema\{
    Button,
    Form,
    Grid,
    Section,
    Select,
    TextInput,
    Toggle,
};

class CreateTestPage extends BasePage
{
    public function title(): ?string
    {
        return 'Create test';
    }

    public function schema(): array
    {
        return [
            Form::make()
                ->action(admin_path('tests'))
                ->method('POST')
                ->schema([
                    Section::make('Details')
                        ->description('Basic test information.')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')->label('Name')->required(),
                                Select::make('status')->options([
                                    'active' => 'Active',
                                    'draft' => 'Draft',
                                ]),
                            ]),
                            Toggle::make('featured')->label('Featured'),
                        ]),
                    Button::make('Save')->submit(),
                ]),
        ];
    }
}
```

Pass the page to Inertia from a controller:

```php
public function create(CreateTestPage $page)
{
    abort_unless($page->authorize(), 403);

    return Inertia::render('Admin/CreateTest', $page->toInertia([
        'breadcrumbs' => ['Tests', 'Create'],
    ]));
}
```

Available layout and content components are `Form`, `Section`, `Grid`, `Card`, `Tabs`, `Tab`, `Heading`, `Text`, and `Button`.

Available fields are `TextInput`, `Select`, `Toggle`, `Textarea`, `NumberInput`, `Checkbox`, and `DateTimeInput`.

All components support `label()`, `helpText()`, `width()`, `columnSpan()`, `visibleWhen()`, and `disabledWhen()`. `Select` also supports `optionsApi($url, $dependsOn)` for dependent options. `Button::submit()` is shorthand for `type('submit')`.

## Tables

Define table metadata in a `BaseResource`. Set `$model` to a model class name; do not instantiate the model.

```php
<?php

namespace App\AdminPanel\Resources;

use App\AdminPanel\Engine\BaseResource;
use App\AdminPanel\Tables\{
    Action,
    BadgeColumn,
    Search,
    SelectFilter,
    Tab,
    Tabs,
    TextColumn,
};
use App\Models\Test;

class TestResource extends BaseResource
{
    protected string $key = 'tests';
    protected string $model = Test::class;

    public function schema(): array
    {
        return [
            'search_placeholder' => 'Search tests...',
            'search_columns' => [Search::column('name')->weight(3)],
            'tabs' => Tabs::make([
                Tab::make('all'),
                Tab::make('active')
                    ->query(fn ($query) => $query->where('status', 'active'))
                    ->color('success'),
            ]),
            'columns' => [
                TextColumn::make('name')->label('Name')->sortable(),
                BadgeColumn::make('status')->colors([
                    'success' => 'active',
                    'warning' => 'draft',
                ]),
            ],
            'filters' => [
                SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'draft' => 'Draft',
                ]),
            ],
            'actions' => [
                Action::make('edit')
                    ->url(fn (array $record) => admin_path("tests/{$record['uuid']}/edit")),
                Action::make('delete')
                    ->delete(fn (array $record) => admin_path("tests/{$record['uuid']}")),
            ],
            'settings' => [
                'record_selection' => true,
                'selection_column' => 'uuid',
            ],
        ];
    }
}
```

Public columns are `TextColumn`, `BadgeColumn`, `BooleanColumn`, and `ImageColumn`. Use `SelectFilter` for select filters, `Search::column()` for searchable fields, and `Action` for links, API calls, confirmation dialogs, visibility, and disabled state.

Return table data from a controller:

```php
public function index(
    Request $request,
    DataGridEngine $engine,
    TestResource $resource,
) {
    return Inertia::render('Admin/ResourceIndex', [
        'resource' => $engine->handle($resource, $request),
        'title' => 'Tests',
        'description' => 'Sample table resource powered by the DataGrid engine.',
        'createUrl' => admin_path('tests/create'),
        'createLabel' => 'Add test',
    ]);
}
```

Use a resource-level `query` closure when every table query needs an additional constraint:

```php
'query' => fn ($query) => $query->where('is_visible', true),
```

The grid engine does not apply tenant constraints automatically. Multi-tenancy or ownership rules must be explicit in the resource query, a model global scope, or authorization policy.

## Configuration

Shared settings (languages, uploads, table defaults) live in `config/admin.php`. Panel branding, prefix, and middleware are registered in `App\Providers\AdminPanelProvider` via `PanelRegistry`.

```env
ADMIN_NAME="Admin Panel"
ADMIN_PREFIX=admin
ADMIN_LOCALE=en
ADMIN_MAX_UPLOAD_KB=4096
ADMIN_TABLE_PER_PAGE=25
```

Change the URL prefix anytime:

```env
ADMIN_PREFIX=dashboard
```

Routes become `/dashboard`, `/dashboard/login`, `/dashboard/tests`, etc.

Helpers (use these instead of hardcoding paths):

```php
admin_prefix();          // "admin"
admin_path('tests');     // "/admin/tests"
admin_url('tests');      // "https://example.test/admin/tests"
admin_home();            // "/admin"
```

On the frontend, use:

```ts
import { useAdminConfig } from '@/composables/useAdminConfig';

const { adminPath } = useAdminConfig();
adminPath('tests'); // /admin/tests
```

## UI

The admin UI is built with **shadcn/vue** (local components in `resources/js/components/ui`, not a CDN). Schema nodes render through `SchemaRenderer` and tables through `DataTable`.

## Extending the system

Add backend component implementations under `app/AdminPanel/Engine/Components`, then expose application-facing names under `app/AdminPanel/Schema`.

New rendered component types also need a matching entry in `resources/js/components/Admin/PageBuilder/Registry.ts`. Keep serialized schemas JSON-safe: resolve database queries and record-specific closures before sending data to Inertia.
