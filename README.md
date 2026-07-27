# Admin Panel Builder

Laravel 12 + Inertia + Vue 3 admin kit. You define tables and forms in PHP; the UI renders them automatically.

This repo ships as a **clean kit** — no panels or demo resources. Follow the steps below to create your first panel and a Posts CRUD.

**Login (after seed):** `admin@example.com` / `password`

**Full Schema / DataGrid component reference:** [SDUI_DOCUMENTATION.md](./SDUI_DOCUMENTATION.md) — every page layout, field, view UI, and table column.

---

## 1. Install the project

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then:
php artisan migrate --seed
php artisan storage:link   # required for public file uploads (/storage/…)

npm install
npm run dev

php artisan serve
```

`migrate --seed` creates the admin user (`avatar` defaults to `/placeholder/avatar-placeholder.png`). `storage:link` makes files on the `public` disk available at `/storage/...`. Keep `npm run dev` (or `npm run build`) running while you work.

---

## 2. Create your first panel

A **panel** is one admin area (URL prefix, middleware, branding, sidebar menu).

```bash
php artisan make:admin-panel admin
```

This scaffolds a **complete starter panel** and registers it in `app/Providers/AdminPanelProvider.php`:

| File | Purpose |
|------|---------|
| `app/AdminPanel/Panels/AdminPanel.php` | Settings + sidebar `menu()` |
| `routes/panels/admin.php` | Dashboard, profile, users routes |
| `…/Pages/Admin/DashboardPage.php` + `DashboardController` | Dashboard with charts |
| `…/Pages/Admin/ProfilePage.php` + `ProfileController` | Profile (account + delete) |
| `…/Resources/Admin/UserResource.php` | Users DataGrid + schema header |
| `…/Pages/Admin/UserFormPage.php` / `UserViewPage.php` | Create / edit / view |
| `…/Http/Controllers/Admin/UserController.php` | Users CRUD |

Every generated page includes a **heading + description**, cards, and spacing (`Space` / optional mobile bottom-bar actions).

### Middleware (manual)

`make:admin-panel` does **not** create middleware. Apply existing middleware yourself on the panel class. Typical stack:

| Middleware | Role |
|------------|------|
| `auth` | Must be logged in |
| `admin` | Requires `users.is_admin` (see `AdminMiddleware`) |
| `panel:{id}` | Sets the active panel for `admin_path()` / menu |

```php
// app/AdminPanel/Panels/AdminPanel.php
$this
    ->prefix('admin')
    // Add 'admin' only when the panel should be is_admin-only:
    ->middleware(['auth', 'admin', 'panel:admin'])
    ->name('Admin Panel')   // sidebar brand title
    ->logo('/placeholder/logo-placeholder.png');
```

Open [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login) and sign in → `/admin` (dashboard), `/admin/users`, `/admin/profile`.

### Extra panels

```bash
php artisan make:admin-panel vendor --prefix=vendor
```

Each panel gets its own class, routes file, and entry in `AdminPanelProvider::$panels`.

### Panel switcher (user menu)

The sidebar user menu lists every panel that is **not** marked hidden. Click a panel to jump to its home URL.

```php
$this
    ->prefix('vendor')
    ->middleware(['auth', 'panel:vendor'])
    ->name('Vendor Panel')
    ->hidden(); // not shown in the switcher
```

- **`->hidden()`** — hides the panel from the user-menu list only  
- **Middleware** still controls access. A visible panel can still return **403 Unauthorized** if the user fails `admin` / other guards when they open the URL

### Path helpers (`admin_path`, …)

These helpers always target the **current panel** (set by the `panel:{id}` middleware on that request). You do **not** hardcode `/admin` in menus, forms, or actions.

| Helper | Example | Result (on `/admin/…`) |
|--------|---------|-------------------------|
| `admin_panel()` | `admin_panel()` | `"admin"` (active panel key) |
| `admin_prefix()` | `admin_prefix()` | `"admin"` |
| `admin_path('posts')` | `admin_path('posts')` | `"/admin/posts"` |
| `admin_url('posts')` | `admin_url('posts')` | full URL under that prefix |
| `admin_home()` | `admin_home()` | panel home, e.g. `"/admin"` |
| `admin_home_for($user)` | `admin_home_for()` | home for `user.default_panel` (login redirect) |
| `admin_menu()` | `admin_menu()` | sidebar items for the current panel |

Force another panel with the optional second argument:

```php
admin_path('posts', 'vendor'); // "/vendor/posts"
```

Outside a panel request (login, artisan, etc.), helpers fall back to `config('admin.default')` (usually `admin`).

Use them everywhere you need a panel URL — menu links, form actions, DataGrid actions, redirects — so the same code works if you rename the prefix or run under another panel.

### Default panel (login redirect)

Each user can store a **default panel prefix** (or panel id) in `users.default_panel`. After login, they are redirected to that panel’s home.

```php
// users.default_panel = "admin"  → /admin
// users.default_panel = "vendor" → /vendor
$user->default_panel = 'vendor';
$user->save();
```

The seeded admin uses `default_panel = admin`:

```php
User::updateOrCreate(
    ['email' => 'admin@example.com'],
    [
        // …
        'is_admin' => true,
        'default_panel' => 'admin',
    ],
);
```

Login uses `admin_home_for($user)` (resolves prefix/id via `PanelRegistry`, then `admin_home()`). If the value is missing or unknown, it falls back to the registry default panel.

---

## 3. Create a Post model + migration

Admin generators **do not** create models or migrations. Create them yourself first.

```bash
php artisan make:model Post -m
```

Edit the migration:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->string('status')->default('draft'); // draft | published
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

Edit `app/Models/Post.php`:

```php
protected $fillable = [
    'title',
    'description',
    'image',
    'status',
    'is_active',
];

protected function casts(): array
{
    return [
        'is_active' => 'boolean',
    ];
}
```

Run:

```bash
php artisan migrate
```

---

## 4. Create a resource + pages

### Commands overview

| Command | What it does |
|---------|----------------|
| `make:admin-panel {name}` | Panel + Dashboard, Profile, Users CRUD (form + view) |
| `make:admin-resource {name} --panel=` | Table resource + controller (CRUD list) |
| `make:admin-resource … --form` | Also create/edit form page |
| `make:admin-resource … --view` | Also show/view page |
| `make:admin-table {name} --panel=` | Resource only |
| `make:admin-page {name} --panel=` | Standalone schema page |

**Important:** these commands do **not** create models or migrations.

### Generate Posts for the admin panel

```bash
php artisan make:admin-resource Post --panel=admin --form --view
```

Creates:

- `app/AdminPanel/Resources/Admin/PostResource.php` — DataGrid (table)
- `app/Http/Controllers/Admin/PostController.php`
- `app/AdminPanel/Pages/Admin/PostFormPage.php` — create / edit
- `app/AdminPanel/Pages/Admin/PostViewPage.php` — show

### Add routes

Paste into `routes/panels/admin.php` and **save the file** (the command prints this):

```php
Route::post('/posts/bulk', [\App\Http\Controllers\Admin\PostController::class, 'bulk'])
    ->name('admin.posts.bulk');
Route::resource('posts', \App\Http\Controllers\Admin\PostController::class)
    ->names('admin.posts');
```

Confirm routes are loaded:

```bash
php artisan route:list --path=admin/posts
```

### Add a sidebar link

In `app/AdminPanel/Panels/AdminPanel.php` → `menu()`:

```php
use App\AdminPanel\Menu\MenuItem;
use App\AdminPanel\Menu\PanelMenu;

public function menu(): array
{
    return PanelMenu::make()
        ->default()
        ->section('content', [
            MenuItem::link('posts', admin_path('posts'))
                ->icon('heroicons:rectangle-stack')
                ->title('Posts')
                // ->suffix(value: '10', type: 'badge', color: 'danger')
                // ->suffix(value: 'heroicons:bolt', type: 'icon', color: 'warning')
                // ->disabled()
                ,
        ])
        ->build();
}
```

Icons come from **[Iconify](https://icon-sets.iconify.design/)**. Use `collection:icon-name` (e.g. `heroicons:rectangle-stack`, `mdi:home`, `lucide:settings`).

Open `/admin/posts`.

### Authorization

Put auth checks in **controllers** (or policies / middleware) — not on page or resource classes.

- **Panel middleware** — chosen manually on the panel (see above); e.g. `admin` requires `is_admin`  
- **Per-action** — gate `store` / `update` / `destroy` / `index` in the controller as needed  

### Page `$data` pattern

Form / view pages take a single `$data` bag (array from the controller → object on the page). Action/method are hardcoded inside the page from `type` + `id`.

```php
// create
$page = new PostFormPage(['type' => 'create']);

// edit
$page = new PostFormPage(array_merge($post->only([...]), [
    'type' => 'edit',
    'id' => $post->id,
]));

// view
$page = new PostViewPage(array_merge($post->toArray(), ['type' => 'view']));
```

Inside the page: `$this->data->type`, `$this->data->title`, etc.

---

## 5. Customize fields (form + view + table)

Scaffolded resources include **status tabs** and an **is_active** select filter by default. For Posts, also wire **title / description / image / status / is_active** in the form.

### Create & edit — `PostFormPage`

```php
public function __construct(array|object $data = [])
{
    $this->data = is_array($data) ? (object) $data : $data;
}

public function schema(): array
{
    $isCreate = ($this->data->type ?? null) === 'create';

    return [
        // …heading…
        Form::make()
            ->action($isCreate ? admin_path('posts') : admin_path('posts/'.$this->data->id))
            ->method($isCreate ? 'POST' : 'PUT')
            ->schema([
                Card::make()->border()->label('Post')->schema([
                    TextInput::make('title')->label('Title')->required(),
                    Textarea::make('description')->label('Description')->rows(5),
                    FileInput::make('image')->label('Image')->image(),
                    Select::make('status')->label('Status')->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])->required(),
                    Toggle::make('is_active')->label('Active'),
                ]),
                Flex::make()->justify('end')->schema([
                    Button::make('Save')->submit()->showOnBottomBar(),
                ]),
            ]),
    ];
}

public function initialData(): array
{
    return [
        'title' => $this->data->title ?? '',
        'description' => $this->data->description ?? '',
        'image' => $this->data->image ?? '', // stored path string
        'image_file' => null,
        'status' => $this->data->status ?? 'draft',
        'is_active' => (bool) ($this->data->is_active ?? true),
    ];
}
```

### Create & update — `PostController` + `FileUploadService`

FileInput submits the file as `{field}_file` (e.g. `image_file`). The form must include that key in initial data so Inertia sends it. Schema forms automatically send **PUT/PATCH as POST + `_method`** when uploading files (PHP cannot parse multipart PUT bodies). Upload with `App\Services\FileUploadService` **before** saving the model:

```php
use App\Services\FileUploadService;

public function store(Request $request, FileUploadService $uploads)
{
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'image_file' => ['nullable', 'image', 'max:2048'],
        'status' => ['required', 'in:draft,published'],
        'is_active' => ['sometimes', 'boolean'],
    ]);

    $validated['is_active'] = $request->boolean('is_active');

    if ($request->hasFile('image_file')) {
        $validated['image'] = $uploads->upload($request->file('image_file'), 'posts');
    }

    Post::create($validated);
    Notify::success('Post created.');

    return redirect()->route('admin.posts.index');
}

public function update(Request $request, Post $post, FileUploadService $uploads)
{
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'image_file' => ['nullable', 'image', 'max:2048'],
        'status' => ['required', 'in:draft,published'],
        'is_active' => ['sometimes', 'boolean'],
    ]);

    $validated['is_active'] = $request->boolean('is_active');

    if ($request->hasFile('image_file')) {
        $uploads->delete($post->image); // remove old file
        $validated['image'] = $uploads->upload($request->file('image_file'), 'posts');
    }

    $post->update($validated);
    Notify::success('Post updated.');

    return redirect()->route('admin.posts.index');
}
```

On delete, remove the file too:

```php
public function destroy(Post $post, FileUploadService $uploads)
{
    $uploads->delete($post->image);
    $post->delete();
    Notify::success('Post deleted.');

    return back();
}
```

`$uploads->upload()` stores on the `public` disk (e.g. `posts/uuid.jpg`) and returns that relative path. With `php artisan storage:link`, the file is served at `/storage/posts/uuid.jpg`.

### View (show) — `PostViewPage`

Put **Edit** / **Back** in a top flex row. Use `KeyValue` for text attributes and a separate `Image` block under the card (KeyValue is string→string and will not render uploads as pictures).

```php
use App\AdminPanel\Schema\{Button, Card, Flex, Heading, Image, KeyValue, Text};

public function schema(): array
{
    $record = $this->post;

    return [
        Flex::make()->justify('between')->schema([
            Heading::make($record->title ?? 'Post')->level(2),
            Flex::make()->gap(2)->schema([
                Button::make('Edit')
                    ->variant('outline')
                    ->url(admin_path('posts/'.$record->getKey().'/edit')),
                Button::make('Back')
                    ->variant('secondary')
                    ->url(admin_path('posts')),
            ]),
        ]),
        Card::make()->border()->schema([
            Text::make($record->description ?? '')->variant('body'),
            KeyValue::make()->entries([
                'Title' => $record->title,
                'Description' => $record->description,
                'Status' => $record->status,
                'Active' => $record->is_active ? 'Yes' : 'No',
                'Date' => $record->created_at?->format('M d, Y'),
            ]),
        ]),
        Image::make($record->image)->label('Image'), // auto public URL
    ];
}
```

### DataGrid (index table) — `PostResource`

Index pages render a schema **`header()`** above the table (title, actions, cards, …), then the DataGrid. Controllers use `$resource->toIndexProps($engine->handle(...))`.

Generated resources include status **tabs** and an **is_active** filter. Example for Posts:

```php
use App\AdminPanel\Tables\{
    Action, BadgeColumn, BooleanColumn, BulkAction, ImageColumn,
    Search, SelectFilter, Tab, Tabs, TextColumn
};

public function schema(): array
{
    return [
        'search_placeholder' => 'Search posts…',
        'search_columns' => [
            Search::column('title')->weight(3),
            Search::column('description')->weight(1),
        ],
        'tabs' => Tabs::make([
            Tab::make('all'),
            Tab::make('draft')
                ->query(fn ($q) => $q->where('status', 'draft'))
                ->color('warning'),
            Tab::make('published')
                ->query(fn ($q) => $q->where('status', 'published'))
                ->color('success'),
        ]),
        'columns' => [
            TextColumn::make('id')->label('ID')->sortable(),
            ImageColumn::make('image')->label('Image')->rounded(), // auto public URL
            TextColumn::make('title')->label('Title')->sortable(),
            TextColumn::make('description')->label('Description')->toggleable(),
            BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'warning' => 'draft',
                    'success' => 'published',
                ]),
            BooleanColumn::make('is_active')->label('Active'),
            TextColumn::make('created_at')
                ->label('Created')
                ->sortable()
                ->transform(fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('M d, Y') : null),
        ],
        'filters' => [
            SelectFilter::make('is_active')
                ->label('Active')
                ->options([
                    '1' => 'Active',
                    '0' => 'Inactive',
                ]),
        ],
        'actions' => [
            Action::make('view')->url(fn ($r) => admin_path('posts/'.$r['id'])),
            Action::make('edit')->url(fn ($r) => admin_path('posts/'.$r['id'].'/edit')),
            Action::make('delete')->delete(fn ($r) => admin_path('posts/'.$r['id'])),
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

Toolbar **Filters** and **Columns** controls are icon-only. Image columns resolve storage paths to public `/storage/...` URLs automatically.

### Useful inputs (forms)

| Component | Example |
|-----------|---------|
| `TextInput` | `TextInput::make('title')->required()` |
| `Textarea` | `Textarea::make('description')->rows(5)` |
| `FileInput` | `FileInput::make('image')->image()` |
| `NumberInput` | `NumberInput::make('price')->min(0)` |
| `Select` | `Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])` |
| `Toggle` | `Toggle::make('featured')->label('Featured')` |
| `Checkbox` | `Checkbox::make('agree')` |
| `DateTimeInput` | `DateTimeInput::make('published_at')->withTime()` |
| `MultiSelect` | `MultiSelect::make('categories')->options([...])` |
| `TagsInput` | `TagsInput::make('tags')->suggestions(['news', 'product'])->max(12)` |

### Useful columns (DataGrid)

| Component | Example |
|-----------|---------|
| `TextColumn` | `TextColumn::make('title')->sortable()` — click header to sort |
| `ImageColumn` | `ImageColumn::make('image')->rounded()` |
| `BadgeColumn` | `BadgeColumn::make('status')->colors(['success' => 'published'])` |
| `BooleanColumn` | `BooleanColumn::make('featured')` |

- **`->sortable()`** — column header is clickable; sends `sort_by` / `sort_order` to the server  
- **`->toggleable()`** — column appears in the **Columns** menu so users can show/hide it  

Layout helpers for forms: `Card`, `Grid`, `Flex`, `Section`, `Tabs`.

View helpers: `Heading`, `Text`, `KeyValue`, `Chart`, `Space`, `Image::make($path)->label('Cover')` (resolves storage paths to public URLs).

Mobile bottom actions (opt-in): `Button::make('Save')->submit()->showOnBottomBar()` — on small screens the button moves into a fixed bottom bar and is removed from its inline spot. Desktop stays inline.

Buttons default to `type="button"`. Only `->submit()` (or `->type('submit')`) makes them submit the form.

### Charts (ApexCharts)

Schema-driven charts via `Chart::make()` → Vue `SchemaChart` + [ApexCharts](https://apexcharts.com/).

```php
use App\AdminPanel\Schema\{Card, Chart, Grid, Heading};

public function schema(): array
{
    return [
        Heading::make('Reports')->level(2),
        Grid::make(2)->schema([
            Chart::make()
                ->border()
                ->label('Signups')
                    ->type('area')          // line | area | bar | column | pie | donut | …
                ->height(280)
                ->categories(['Mon', 'Tue', 'Wed', 'Thu', 'Fri'])
                ->series([
                    ['name' => 'Users', 'data' => [4, 7, 5, 12, 9]],
                ]),

            Chart::make()
                ->border()
                ->label('Roles')
                ->type('donut')
                ->height(280)
                ->labels(['Admin', 'User'])
                ->series([12, 88]),

            // Or load data from an API (shows a spinner while fetching)
            Chart::make()
                ->border()
                ->label('Signups (live)')
                ->type('area')
                ->height(280)
                ->api('/admin/api/signups'),
        ]),
    ];
}
```

API JSON shape (also accepted under a `data` key):

```json
{
  "series": [{ "name": "Users", "data": [4, 7, 5, 12, 9] }],
  "categories": ["Mon", "Tue", "Wed", "Thu", "Fri"]
}
```

| Method | Purpose |
|--------|---------|
| `->type('area')` | Apex chart type: `line`, `area`, `bar` (horizontal), `column` (vertical bars), `pie`, `donut`, `radialBar` |
| `->height(320)` | Chart height in px |
| `->series([...])` | Line/area/bar: `[['name' => '…', 'data' => […]]]`; pie/donut: `[44, 55]` |
| `->categories([...])` | X-axis labels (cartesian charts) |
| `->labels([...])` | Slice labels (pie / donut) |
| `->colors([...])` | Optional color overrides (defaults use theme tokens) |
| `->options([...])` | Raw ApexCharts options (deep-merged) |
| `->api('/path')` | GET JSON for `series` (+ optional `categories` / `labels` / `colors` / `options`); shows loading spinner |
| `->sparkline()` | Compact sparkline mode |
| `->toolbar()` | Show Apex toolbar |
| `->border()` | Wrap in `admin-surface` card |

Generated panel dashboards include sample area + donut charts. Vue registration: `vue3-apexcharts` in `resources/js/app.ts`; node: `resources/js/components/Admin/Schema/nodes/SchemaChart.vue`.

### Languages & translations

UI copy lives in the **backend**. Schema pages, menus, notifications, and form labels are already rendered as strings in PHP — do **not** ship translation JSON bags through Inertia.

Use Laravel’s default helper:

```php
__('admin.dashboard')
__('content.login')
```

Add languages in `config/admin.php` (sidebar switcher + fonts):

```php
'languages' => [
    [
        'label' => 'English',
        'locale' => 'en',
        'family' => 'Plus Jakarta Sans',
        'font' => 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:…',
    ],
    [
        'label' => 'Arabic',
        'locale' => 'ar',
        'family' => 'Cairo',
        'font' => 'https://fonts.googleapis.com/css2?family=Cairo:…',
    ],
    // Add more locales here…
],
'default_locale' => 'en',
```

Then add matching files under `resources/lang/{locale}/` (e.g. `admin.php`, `content.php`) and wrap strings with `__('…')` in pages, menus, controllers, and stubs. The locale switcher sets `App::setLocale()` via session/cookie; `__()` resolves on the next request.

Example menu title:

```php
MenuItem::link('posts', admin_path('posts'))
    ->icon('heroicons:rectangle-stack')
    ->title(__('admin.posts')),
```

### Theme (colors & roundness)

Edit CSS tokens — no Tailwind config file:

| File | What to change |
|------|----------------|
| `resources/css/theme/tokens.css` | `--radius` + light colors (`--primary`, `--sidebar`, …) |
| `resources/css/theme/dark.css` | Dark mode overrides |
| `resources/css/theme/base.css` | Body / base styles |
| `resources/css/theme/utilities.css` | `.admin-surface` |

Change `--radius` in `tokens.css` to scale all `rounded-*` utilities.

Panel navigation loader (pagination, visits, etc.) waits `config('admin.ui.loading_delay_ms')` (default **200**, env `ADMIN_LOADING_DELAY_MS`) before showing — fast requests feel instant.

**See also:** complete catalog of page components, field options, and DataGrid UI in [SDUI_DOCUMENTATION.md](./SDUI_DOCUMENTATION.md).

---

## Quick checklist

```bash
# 1. Install
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate --seed && php artisan storage:link && npm install && npm run dev

# 2. First panel
php artisan make:admin-panel admin
# Edit AdminPanel: middleware ['auth', 'admin', 'panel:admin']

# 3. Model
php artisan make:model Post -m
php artisan migrate

# 4. Resource + pages
php artisan make:admin-resource Post --panel=admin --form --view

# 5. Add routes + menu, then customize form for title / description / image / status / is_active
```

Login: **admin@example.com** / **password** → `/admin` → `/admin/posts`
