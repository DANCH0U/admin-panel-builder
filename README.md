# Admin Panel Builder

Laravel 12 + Inertia + Vue 3 kit for building admin panels with a PHP schema API (SDUI). Define tables, forms, and pages in PHP; the frontend renders them with shadcn/vue.

**Admin URL:** `/admin`  
**Login:** `admin@example.com` / `password` (user with `is_admin=true`)

---

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

Open `/admin` and sign in.

### Seeded test data

`php artisan migrate --seed` (or `php artisan db:seed`) runs:

| Seeder | What it creates |
|--------|-----------------|
| `DatabaseSeeder` | Admin user + default `panel_settings` rows |
| `TestSeeder` | Sample **Tests** records for the DataTable demo |

Admin credentials: `admin@example.com` / `password`.

Re-seed tests only:

```bash
php artisan db:seed --class=TestSeeder
```

---

## Artisan commands

### `make:admin-resource` (recommended)

Scaffolds a CRUD slice for this system (**does not** create Eloquent models or database migrations — create those yourself first).

```bash
# Table resource + controller + Vue index
php artisan make:admin-resource Post

# + create/edit form page (PostFormPage) and controller methods
php artisan make:admin-resource Post --form

# + show/view page (PostViewPage)
php artisan make:admin-resource Post --view

# Both form and view
php artisan make:admin-resource Post --form --view

# Custom model
php artisan make:admin-resource Post --model=App\\Models\\Post --form --view

# Overwrite existing files
php artisan make:admin-resource Post --form --view --force
```

**Creates:**

| File | When |
|------|------|
| `app/AdminPanel/Resources/PostResource.php` | always |
| `app/Http/Controllers/Admin/PostController.php` | always |
| `resources/js/pages/Admin/Posts/Index.vue` | always |
| `app/AdminPanel/Pages/PostFormPage.php` | `--form` |
| `app/AdminPanel/Pages/PostViewPage.php` | `--view` |

**Does not create:** models, migrations, factories, or seeders. Point `--model` at an existing model (or create the model/migration first).

Then add the routes printed by the command to `routes/admin.php`, and a sidebar item in the panel menu class (e.g. `App\AdminPanel\Menus\AdminMenu`).

### `make:admin-table` / `make:admin-page`

Same rule: **no models or migrations** — only AdminPanel PHP/Vue scaffolding.

```bash
php artisan make:admin-table Product --model=App\\Models\\Product
php artisan make:admin-page Settings
```

---

## Architecture

```
app/AdminPanel/
  Schema/          Form fields, layout, UI (public API)
  Tables/          Columns, filters, tabs, actions, bulk actions
  Pages/           Schema-driven pages (forms, views, settings)
  Resources/       DataGrid table definitions
  Menus/           Per-panel sidebar menu classes
  Menu/            PanelMenu / MenuItem builders
  Notifications/   Notify + FlashBag (Inertia toasts)
  Engine/          DataGridEngine, query pipeline (internals)

app/Models/PanelSetting.php   Per-panel branding (DB)
app/Http/Middleware/ResolveAdminPanel.php
```

**Public namespaces to import:**

- `App\AdminPanel\Schema\*` — forms & pages
- `App\AdminPanel\Tables\*` — table columns / filters / actions
- `App\AdminPanel\Engine\BasePage`, `BaseResource`, `DataGridEngine`
- `App\AdminPanel\Notifications\Notify`
- `App\AdminPanel\Menu\{PanelMenu, MenuItem}`

---

## Multi-panel

Panels are defined in `config/admin.php` under `panels`. Each panel has its own:

- URL `prefix`
- `middleware` (must include `panel:{key}`)
- `menu` class
- `panel_settings` DB row (branding)

```php
'panels' => [
    'admin' => [
        'name' => 'Admin Panel',
        'prefix' => 'admin',
        'middleware' => ['auth', 'admin', 'panel:admin'],
        'menu' => \App\AdminPanel\Menus\AdminMenu::class,
        'ui' => [ /* defaults used when seeding panel_settings */ ],
    ],
],
```

`ResolveAdminPanel` (`panel:admin`) binds the current panel so helpers resolve correctly:

```php
admin_panel();              // "admin"
admin_panel_config();       // config row
admin_prefix();             // "admin"
admin_path('tests');        // "/admin/tests"
admin_menu();               // sidebar from the panel's menu class
admin_settings();           // PanelSetting for this panel
```

To add a second panel: copy a panel config entry, create a menu class, add a route group with `panel:yourKey`, and seed will create its `panel_settings` row.

---

## Shared Inertia props

Shared data is intentionally small. One `panel` bag replaces the old `admin` / `settings` / `context` duplicates:

```js
page.props.panel // {
  key, name, prefix, path,
  logo_url, navbar_title, show_theme_toggle,
  locale, language, languages, menu
}
```

Also shared: `auth`, `translations`, `notifications`.

---

## Panel settings (database)

Branding lives in `panel_settings` (one row per panel key), not a JSON file.

| Path | Purpose |
|------|---------|
| `/admin/settings` | App name, logo, theme toggle → `panel_settings` for current panel |
| `/admin/profile` | Name, email, password, delete account |

---

## Tables (DataGrid)

```php
use App\AdminPanel\Tables\{
    Action, BulkAction, BadgeColumn, Search, SelectFilter, Tab, Tabs, TextColumn
};

public function schema(): array
{
    return [
        'search_placeholder' => 'Search…',
        'search_columns' => [
            Search::column('name')->weight(3),
        ],
        'tabs' => Tabs::make([
            Tab::make('all'),
            Tab::make('active')->query(fn ($q) => $q->where('status', 'active'))->color('success'),
        ]),
        'columns' => [
            TextColumn::make('name')->label('Name')->sortable(),
        ],
        'filters' => [
            SelectFilter::make('status')->options(['active' => 'Active']),
        ],
        'actions' => [
            Action::make('edit')->url(fn ($r) => admin_path("posts/{$r['id']}/edit")),
            Action::make('delete')->delete(fn ($r) => admin_path("posts/{$r['id']}")),
        ],
        'bulk_actions' => [
            BulkAction::make('delete')
                ->label('Delete selected')
                ->delete(fn (array $ids) => Post::whereIn('id', $ids)->delete()),
        ],
        'settings' => [
            'record_selection' => true,
            'selection_column' => 'id',
            'bulk_url' => admin_path('posts/bulk'),
        ],
    ];
}
```

Controller index:

```php
return Inertia::render('Admin/Posts/Index', [
    'resource' => DataGridEngine::make()->handle(new PostResource(), $request),
]);
```

Bulk UI: **Bulk actions** dropdown (before Filters). Select rows with the checkbox column, then run a bulk action.

---

## Forms & pages

```php
use App\AdminPanel\Schema\{
    Button, Card, Flex, Form, Grid, TextInput, Select, Toggle
};

Form::make()
    ->action(admin_path('posts'))
    ->method('POST')
    ->schema([
        Card::make()->border()->label('Details')->schema([
            Grid::make(2)->schema([
                TextInput::make('name')->label('Name')->required(),
                Select::make('status')->options(['draft' => 'Draft', 'active' => 'Active']),
            ]),
            Toggle::make('featured')->label('Featured'),
        ]),
        Flex::make()->justify('end')->gap(3)->schema([
            Button::make('Save')->submit(),
        ]),
    ]);
```

- `Form` / `Card` are borderless by default — call `->border()` for card chrome.
- `Flex::make()->gap(4)` maps to Tailwind `gap-*`.

```php
return Inertia::render('Admin/SchemaPage', $page->toInertia([
    'initialData' => $page->initialData(),
]));
```

---

## Notifications

```php
Notify::success('Saved');
Notify::success('Saved')->action('View', admin_path('posts'));
notify('success', 'Saved');
```

Demo: `/admin/demo/notifications/success?action=1`

---

## Menu

Per-panel menu class (preferred):

```php
// app/AdminPanel/Menus/AdminMenu.php
PanelMenu::make()
    ->default()
    ->section('builder', [
        MenuItem::link('tests', admin_path('tests'))
            ->icon('heroicons:beaker')
            ->title('Tests'),
    ])
    ->section('settings', [
        MenuItem::link('panel_settings', admin_path('settings'))
            ->icon('heroicons:cog-6-tooth'),
    ])
    ->build();
```

User menu (sidebar footer): **Profile**, **Light/Dark**, **Language**, **Log out**.

---

## Languages & fonts

Configure in `config/admin.php`. Each entry needs `label`, `locale`, `family` (CSS font-family name), and `font` (Google Fonts CSS URL):

```php
'languages' => [
    [
        'label' => 'English',
        'locale' => 'en',
        'family' => 'Plus Jakarta Sans',
        'font' => 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400..700;1,400..700&display=swap',
    ],
    [
        'label' => 'Arabic',
        'locale' => 'ar',
        'family' => 'Cairo',
        'font' => 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap',
    ],
],
```

Runtime fonts set `--admin-font-family` (Tailwind `--font-sans` references it). Switching language recreates the Google Fonts `<link>` so Cairo actually loads.

Switcher: `GET /locale/{locale}?return=…`.

---

## Helpers

```php
admin_panel();
admin_panel_config();
admin_prefix();
admin_path('tests');
admin_url('tests');
admin_home();
admin_menu();
admin_settings();
admin_languages();
admin_language();
admin_font_family();
notify('success', 'Done');
```

---

## Frontend notes

- Schema registry: `resources/js/components/Admin/Schema/registry.ts`
- Tables: `resources/js/components/Admin/Tables/DataTable.vue`
- Theme: `localStorage.theme` + `dark` class on `<html>`
- Build: `npm run build` / `npm run dev`

---

## Testing

```bash
php artisan test
```
