# Admin Panel Builder

Laravel 12 + Inertia + Vue 3 kit for building **admin panels** with a PHP schema API (server-driven UI). You define tables, forms, and pages in PHP; the frontend renders them with shadcn/vue.

**Admin URL:** `/admin`  
**Login:** `admin@example.com` / `password` (`is_admin = true`)

---

## What this project can do

| Area | Capabilities |
|------|----------------|
| **Data tables** | Search, tabs, filters, sortable columns, row actions, bulk actions, pagination, CSV export (engine), relation/nested JSON columns |
| **Forms & pages** | Schema-driven create/edit/view pages — inputs, selects, files, JSON editors, conditionals, layout |
| **Multi-panel** | Multiple panels via `PanelRegistry` (prefix, middleware, menu, branding each) |
| **Branding** | Per-panel name, logo, navbar title, theme toggle (defined in `AdminPanelProvider`) |
| **Auth** | Admin-only login, profile update, delete account |
| **i18n** | Multi-locale + per-language Google Fonts (e.g. English / Arabic + Cairo) |
| **Theme** | Light / dark mode |
| **Toasts** | Success / info / warning / danger notifications with optional action links |
| **Scaffolding** | Artisan generators for panels, resources, tables, and pages (**not** models/migrations) |
| **Demo** | Seeded Tests CRUD showcasing the full stack |

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

### Seeded data

| Seeder | Creates |
|--------|---------|
| `DatabaseSeeder` | Admin user |
| `TestSeeder` | Sample **Tests** records (statuses, tags, JSON metadata, images, …) |

```bash
php artisan db:seed                 # full seed
php artisan db:seed --class=TestSeeder
```

---

## Architecture

```
app/AdminPanel/
  Panel.php / PanelRegistry.php
  Schema/          Forms, layout, fields, UI (public API)
  Tables/          Columns, filters, tabs, actions, bulk actions
  Pages/{Panel}/   Schema-driven pages per panel (forms, views)
  Resources/{Panel}/  DataGrid table definitions per panel
  Menus/           Per-panel sidebar menus
  Menu/            PanelMenu / MenuItem builders
  Notifications/   Notify + FlashBag
  Engine/          BasePage, BaseResource, DataGridEngine (+ pipeline)

app/Providers/AdminPanelProvider.php
app/Http/Controllers/{Panel}/
app/Http/Middleware/ResolveAdminPanel.php
routes/panels/{key}.php

resources/js/
  components/Admin/Schema/   SchemaRenderer + field nodes
  components/Admin/Tables/   DataTable
  layouts/AdminLayout.vue
  pages/Admin/ResourceIndex.vue   # generic table index
  pages/Admin/SchemaPage.vue      # generic form/view page
```

**Import these namespaces:**

- `App\AdminPanel\{Panel, PanelRegistry}`
- `App\AdminPanel\Schema\*`
- `App\AdminPanel\Tables\*`
- `App\AdminPanel\Engine\{BasePage, BaseResource, DataGridEngine}`
- `App\AdminPanel\Notifications\Notify`
- `App\AdminPanel\Menu\{PanelMenu, MenuItem}`

---

## Artisan commands

Generators scaffold AdminPanel PHP only. They **do not** create Eloquent models, migrations, factories, or seeders — create those yourself first. Resource/table/page commands require `--panel=` (registry id or URL prefix).

### `make:admin-panel`

```bash
php artisan make:admin-panel vendor
php artisan make:admin-panel vendor --prefix=vendor
```

Creates a menu class + `routes/panels/{key}.php`, then prints the `PanelRegistry::register(...)` snippet to add in `AdminPanelProvider`.

### `make:admin-resource` (recommended)

Full CRUD slice: resource + controller; optional form/view pages. Index uses generic `Admin/ResourceIndex`.

```bash
php artisan make:admin-resource Post --panel=admin
php artisan make:admin-resource Post --panel=admin --form
php artisan make:admin-resource Post --panel=admin --view
php artisan make:admin-resource Post --panel=admin --form --view
php artisan make:admin-resource Post --panel=admin --model=App\\Models\\Post --form --view
php artisan make:admin-resource Post --panel=admin --form --view --force
```

| File | When |
|------|------|
| `app/AdminPanel/Resources/{Panel}/PostResource.php` | always |
| `app/Http/Controllers/{Panel}/PostController.php` | always |
| `app/AdminPanel/Pages/{Panel}/PostFormPage.php` | `--form` |
| `app/AdminPanel/Pages/{Panel}/PostViewPage.php` | `--view` |

Then wire routes into `routes/panels/{panel}.php` and a menu item in that panel’s menu class (the command prints both).

### `make:admin-table` / `make:admin-page`

```bash
php artisan make:admin-table Product --panel=admin --model=App\\Models\\Product
php artisan make:admin-page Reports --panel=admin
```

Pages render with `Admin/SchemaPage`; tables with `Admin/ResourceIndex`.

---

## Multi-panel

Panels are registered in `App\Providers\AdminPanelProvider` via `PanelRegistry` (not the database):

```php
PanelRegistry::register('admin', function (Panel $panel) {
    $panel
        ->prefix('admin')
        ->middleware(['auth', 'admin', 'panel:admin'])
        ->name('Admin Panel')
        ->logo('/admin-logo.svg')
        ->navbarTitle('Admin Panel')
        ->menu(AdminMenu::class);
});
```

`routes/admin.php` loads each panel’s `routes/panels/{id}.php` under that panel’s prefix + middleware.

`ResolveAdminPanel` (`panel:admin`) binds the current panel. Helpers then resolve prefix, paths, menu, and branding.

**To add a panel:** `php artisan make:admin-panel vendor` → paste the registration into `AdminPanelProvider` → add routes in `routes/panels/vendor.php`.

---

## Auth & profile

| Feature | Details |
|---------|---------|
| **Login** | `/admin/login` — must authenticate and have `is_admin`; lands on `admin_home()` |
| **Logout** | Invalidates session → login |
| **Profile** | `/admin/profile` — name, email, password; delete account with password confirm |
| **Branding** | Defined on the Panel in `AdminPanelProvider` (name, logo, navbar title, theme toggle) |

Middleware: `auth` (guest → login), `admin` (`isAdmin()` or 403), `panel:{key}` (resolve panel).

---

## Shared Inertia props

Kept intentionally small:

```js
page.props.panel // {
  key, name, prefix, path,
  logo_url, navbar_title, show_theme_toggle,
  locale, language, languages, menu
}
page.props.auth
page.props.translations   // content (+ admin when allowed)
page.props.notifications
```

Frontend helpers: `useAdminConfig()` → `adminPath()`, name, logo; `useShellData()` → menu.

---

## Data tables (DataGrid)

Define a `BaseResource` with a `schema()` array. The engine handles query, search, tabs, filters, sort, pagination, CSV export, and bulk actions.

```php
use App\AdminPanel\Tables\{
    Action, BulkAction, BadgeColumn, BooleanColumn, ImageColumn, JsonColumn,
    Search, SelectFilter, Tab, Tabs, TextColumn
};
use App\AdminPanel\Engine\DataGridEngine;

public function schema(): array
{
    return [
        'search_placeholder' => 'Search…',
        'search_columns' => [
            Search::column('name')->weight(3),           // like | exact | fulltext | relation
        ],
        'tabs' => Tabs::make([
            Tab::make('all'),
            Tab::make('active')
                ->query(fn ($q) => $q->where('status', 'active'))
                ->color('success')
                ->showCount(),
        ]),
        'columns' => [
            TextColumn::make('name')->label('Name')->sortable(),
            BadgeColumn::make('status')->colors(['success' => 'active', 'warning' => 'draft']),
            BooleanColumn::make('is_featured')->labels('Yes', 'No'),
            ImageColumn::make('cover_image')->rounded(),
            JsonColumn::make('metadata')->limit(2),
            // Relations / dotted paths:
            // TextColumn::make('author.name')->relationship('author'),
            // TextColumn::make('metadata.0.title'),
        ],
        'filters' => [
            SelectFilter::make('status')->options(['active' => 'Active', 'draft' => 'Draft']),
            // Also available under Tables\Filters\Types\:
            // MultiSelectFilter, BooleanFilter, DateRangeFilter,
            // NumericRangeFilter, RelationFilter
        ],
        'actions' => [
            Action::make('edit')->url(fn ($r) => admin_path("posts/{$r['id']}/edit")),
            Action::make('delete')->delete(fn ($r) => admin_path("posts/{$r['id']}")),
            // Also: ->api($url, $method), ->requiresConfirmation(),
            // ->hiddenIf(), ->disabledIf(), ->can($ability, $model)
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
        // Optional: 'query' => fn ($q) => ..., 'default_sort' => [...], 'query_prefix' => 'posts'
    ];
}
```

Controller:

```php
return Inertia::render('Admin/Posts/Index', [
    'resource' => DataGridEngine::make()->handle(new PostResource(), $request),
]);
```

**UI:** search, colored tabs, filter popover, row action menu, bulk actions dropdown (before Filters), selection checkboxes, pagination.

**Engine also supports** (wire from UI if needed): `sort_by` / `sort_order`, `per_page`, `?export=csv`, optional tab counts via `admin.table.tab_counts`.

Reference implementation: `TestResource` + `/admin/tests`.

---

## Forms & schema pages

Compose UI in PHP; render with `SchemaRenderer` via `Admin/SchemaPage` (or a custom Vue page).

```php
use App\AdminPanel\Schema\{
    Button, Card, Checkbox, DateTimeInput, FileInput, Flex, Form, Grid,
    Heading, JsonCodeInput, JsonInput, KeyValue, ListInput, MultiSelect,
    NumberInput, Section, Select, Tab, Tabs, Text, Textarea, TextInput, Toggle
};

Form::make()
    ->action(admin_path('posts'))
    ->method('POST')
    ->schema([
        Card::make()->border()->label('Details')->schema([
            Grid::make(2)->schema([
                TextInput::make('name')->label('Name')->required(),
                TextInput::make('email')->email(),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'active' => 'Active'])
                    ->searchable(),
                // Select::make('city_id')->optionsApi('/api/cities', ['country_id']),
                MultiSelect::make('tags')->options([...]),
                NumberInput::make('priority')->min(1)->max(5),
                DateTimeInput::make('published_at')->withTime(),
                Toggle::make('featured')->label('Featured'),
                Checkbox::make('agree'),
                FileInput::make('cover')->image()->disk('public'),
            ]),
            ListInput::make('checklist')->addLabel('Add item'),
            JsonInput::make('metadata')->schema([
                TextInput::make('title')->required(),
            ])->addable()->reorderable(),
            JsonCodeInput::make('raw')->rows(8)->pretty(),
            TextInput::make('notes')
                ->visibleWhen('status', 'draft'),
        ]),
        Flex::make()->justify('end')->gap(3)->schema([
            Button::make('Cancel')->back(),
            Button::make('Save')->submit(),
        ]),
    ]);
```

### Layout & UI components

| Component | Role |
|-----------|------|
| `Form` | Submit target + method; optional `->border()` |
| `Card` / `Section` | Group fields (`Section` can be foldable) |
| `Grid` / `Flex` | Columns / flex layout (`gap`, `justify`, `align`, …) |
| `Tabs` / `Tab` | Nested schemas in tabs |
| `Heading` / `Text` | Static typography |
| `Button` | Submit, URL, or back |
| `KeyValue` | Read-only key/value display (view pages) |

Shared field helpers: `label()`, `helpText()`, `placeholder()`, `required()`, `disabled()`, `readonly()`, `default()`, `visibleWhen()`, `disabledWhen()`, `width()`, `columnSpan()`.

### Rendering a page

```php
return Inertia::render('Admin/SchemaPage', $page->toInertia([
    'initialData' => $page->initialData(),
]));
```

See `TestFormPage`, `TestViewPage`, `ProfilePage` for full examples.

---

## Notifications

```php
use App\AdminPanel\Notifications\Notify;

Notify::success('Saved');
Notify::success('Saved')->action('View', admin_path('tests'));
Notify::danger('Failed')->title('Error')->duration(8000);
notify('info', 'Heads up');

// Legacy flash keys still work: ->with('success', '…')
```

Toasts render top-center via `NotificationHost`.  
Demo: `/admin/demo/notifications/success?action=1`

---

## Sidebar menu

Per-panel menu class (preferred):

```php
// app/AdminPanel/Menus/AdminMenu.php
PanelMenu::make()
    ->default() // Overview label + Dashboard
    ->section('builder', [
        MenuItem::link('tests', admin_path('tests'))
            ->icon('heroicons:beaker')
            ->title('Tests'),
    ])
    ->build();
```

Helpers: `MenuItem::link()`, `MenuItem::label()`, `PanelMenu::section()`, `->items()`, `->item()`, `->admin()`.

**User footer menu:** Profile · Light/Dark · Language · Log out.

---

## Languages & fonts

Configured in `config/admin.php` (no enum). Each language needs `label`, `locale`, `family`, and Google Fonts CSS `font`:

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
'default_locale' => env('ADMIN_LOCALE', env('APP_LOCALE', 'en')),
```

- Switcher: `GET /locale/{locale}?return=…` (session + cookie)
- Fonts apply via `--admin-font-family` (Tailwind `font-sans`); locale change recreates the Google Fonts `<link>`
- Translations: `resources/lang/{locale}/admin.php`, `content.php`, `auth.php` + JS lang JSON; Vue `useI18n()` → `t` / `ta` / `tc`

---

## Helpers

```php
admin_panel();                 // current panel key
admin_panel_instance();        // Panel object
admin_panel_config();          // array shape for panel
admin_prefix();                // "admin"
admin_path('tests');           // "/admin/tests"
admin_url('tests');            // absolute
admin_home();                  // post-login path
admin_menu();                  // sidebar items
admin_languages();
admin_locales();
admin_language();
admin_font_family();
notify('success', 'Done');
```

---

## Config reference (`config/admin.php`)

| Key | Purpose |
|-----|---------|
| `default` | Default panel key |
| `languages` / `default_locale` | Locales + fonts |
| `uploads` | Max size, image mimes, disk |
| `table` | Default per-page, options, tab count cache |

Panels (prefix, middleware, name, logo, menu) live in `AdminPanelProvider`, not this config file.

Useful env: `ADMIN_PREFIX`, `ADMIN_NAME`, `ADMIN_DEFAULT_PANEL`, `ADMIN_LOCALE`, `ADMIN_LOGO_URL`, `ADMIN_TABLE_PER_PAGE`, `ADMIN_TABLE_TAB_COUNTS`, `ADMIN_MAX_UPLOAD_KB`, `ADMIN_UPLOAD_DISK`.

---

## Extending the schema system

1. Add an Engine component under `Schema/Fields|Layout|UI`
2. Add a public alias in `Schema/`
3. Register the Vue node in `resources/js/components/Admin/Schema/registry.ts` (`registerSchemaComponent`)
4. Keep serialized schemas JSON-safe (no closures in output)

More detail: `SDUI_DOCUMENTATION.md`.

---

## Frontend notes

- Schema registry: `resources/js/components/Admin/Schema/registry.ts`
- Tables: `resources/js/components/Admin/Tables/DataTable.vue`
- Theme: `localStorage.theme` + `dark` on `<html>`
- UI primitives: local shadcn/vue under `resources/js/components/ui`
- Build: `npm run dev` / `npm run build`

---

## Built-in routes (default `admin` panel)

| Path | Purpose |
|------|---------|
| `/admin` | Dashboard |
| `/admin/login` | Login |
| `/admin/tests` | Sample DataGrid CRUD |
| `/admin/profile` | Account |
| `/admin/demo/notifications/{type}` | Toast demo |
| `/locale/{locale}` | Switch language |

---

## Testing

```bash
php artisan test
```
