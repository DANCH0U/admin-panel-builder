# Admin Panel Builder

Laravel 12 + Inertia + Vue 3 admin kit. You define tables and forms in PHP; the UI renders them automatically.

**Login:** `admin@example.com` / `password`  
**Admin URL:** `/admin`

---

## 1. Install the project

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then:
php artisan migrate --seed

npm install
npm run dev

php artisan serve
```

Open [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin) and sign in.

`migrate --seed` creates the admin user. Frontend needs `npm run dev` (or `npm run build`) while you work.

---

## 2. Create a Post model + migration

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
    $table->timestamps();
});
```

Edit `app/Models/Post.php`:

```php
protected $fillable = [
    'title',
    'description',
    'image',
];
```

Run:

```bash
php artisan migrate
```

---

## 3. Panels — how they work

A **panel** is one admin area (its own URL prefix, middleware, branding, and sidebar menu).

- Classes live in `app/AdminPanel/Panels/`
- They are listed in `app/Providers/AdminPanelProvider.php`
- Routes live in `routes/panels/{key}.php`

The project already ships with **`AdminPanel`** (`/admin`). Open it:

```php
// app/AdminPanel/Panels/AdminPanel.php
class AdminPanel extends Panel
{
    public function __construct()
    {
        parent::__construct('admin');

        $this
            ->prefix('admin')
            ->middleware(['auth', 'admin', 'panel:admin'])
            ->name('Admin Panel')
            ->logo('/admin-logo.svg');
    }

    public function menu(): array
    {
        return PanelMenu::make()
            ->default()
            ->build();
    }
}
```

### Create another panel (optional)

```bash
php artisan make:admin-panel vendor
# or with a custom URL prefix:
php artisan make:admin-panel vendor --prefix=vendor
```

This creates:

| File | Purpose |
|------|---------|
| `app/AdminPanel/Panels/VendorPanel.php` | Settings + `menu()` |
| `routes/panels/vendor.php` | Routes for that panel |

…and registers the class in `AdminPanelProvider::$panels`.

For the Post tutorial below, use the existing **`admin`** panel.

---

## 4. Create a resource + pages

### Commands overview

| Command | What it does |
|---------|----------------|
| `make:admin-panel {name}` | New panel class + routes file |
| `make:admin-resource {name} --panel=` | Table resource + controller (CRUD list) |
| `make:admin-resource … --form` | Also create/edit form page |
| `make:admin-resource … --view` | Also show/view page |
| `make:admin-table {name} --panel=` | Resource only (optional Vue index via older path) |
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

Paste into `routes/panels/admin.php` (the command prints this):

```php
Route::post('/posts/bulk', [\App\Http\Controllers\Admin\PostController::class, 'bulk'])
    ->name('posts.bulk');
Route::resource('posts', \App\Http\Controllers\Admin\PostController::class);
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
                ->title('Posts'),
        ])
        ->build();
}
```

Open `/admin/posts`.

### Authorization

Generated pages and resources include:

```php
public function authorize(): bool
{
    return true; // change this — return false → 403
}
```

- **Resource** `authorize()` — blocks index, bulk actions, delete  
- **Form / view page** `authorize()` — blocks create/edit/show (and store/update via `authorizeOrFail()`)

---

## 5. Customize fields (form + view + table)

Scaffolded files use a `name` field by default. Change them to match **title / description / image**.

### Create & edit — `PostFormPage`

```php
use App\AdminPanel\Schema\{
    Button, Card, FileInput, Flex, Form, Textarea, TextInput
};

public function schema(): array
{
    return [
        Form::make()
            ->action($this->action)
            ->method($this->method)
            ->schema([
                Card::make()->border()->label('Post')->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->required(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(5),

                    FileInput::make('image')
                        ->label('Image')
                        ->image(),
                ]),
                Flex::make()->justify('end')->schema([
                    Button::make('Save')->submit(),
                ]),
            ]),
    ];
}

public function initialData(): array
{
    return [
        'title' => $this->post?->title ?? '',
        'description' => $this->post?->description ?? '',
        'image' => $this->post?->image ?? '',
    ];
}
```

Update validation in `PostController` (`store` / `update`) to use `title`, `description`, `image` instead of `name`.

### View (show) — `PostViewPage`

Use read-only UI like `KeyValue` / `Heading` / `Text`:

```php
use App\AdminPanel\Schema\{Card, Heading, KeyValue, Text};

public function schema(): array
{
    $record = $this->post;

    return [
        Card::make()->border()->schema([
            Heading::make($record->title ?? 'Post')->level(2),
            Text::make($record->description ?? '')->variant('body'),
            KeyValue::make()->entries([
                'Title' => $record->title,
                'Description' => $record->description,
                'Image' => $record->image,
            ]),
        ]),
    ];
}
```

### DataGrid (index table) — `PostResource`

```php
use App\AdminPanel\Tables\{
    Action, BulkAction, ImageColumn, Search, TextColumn
};

public function schema(): array
{
    return [
        'search_placeholder' => 'Search posts…',
        'search_columns' => [
            Search::column('title')->weight(3),
            Search::column('description')->weight(1),
        ],
        'columns' => [
            TextColumn::make('id')->label('ID')->sortable(),
            ImageColumn::make('image')->label('Image')->rounded(),
            TextColumn::make('title')->label('Title')->sortable(),
            TextColumn::make('description')->label('Description')->toggleable(),
            TextColumn::make('created_at')
                ->label('Created')
                ->sortable()
                ->transform(fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('M d, Y') : null),
        ],
        'filters' => [],
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
| `MultiSelect` | `MultiSelect::make('tags')->options([...])` |

### Useful columns (DataGrid)

| Component | Example |
|-----------|---------|
| `TextColumn` | `TextColumn::make('title')->sortable()` |
| `ImageColumn` | `ImageColumn::make('image')->rounded()` |
| `BadgeColumn` | `BadgeColumn::make('status')->colors(['success' => 'published'])` |
| `BooleanColumn` | `BooleanColumn::make('featured')` |

Layout helpers for forms: `Card`, `Grid`, `Flex`, `Section`, `Tabs`.

---

## Quick checklist

```bash
# 1. Install
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate --seed && npm install && npm run dev

# 2. Model
php artisan make:model Post -m
php artisan migrate

# 3. Panel (already have admin; or make another)
# php artisan make:admin-panel vendor

# 4. Resource + pages
php artisan make:admin-resource Post --panel=admin --form --view

# 5. Add routes + menu, then customize fields for title / description / image
```

Login: **admin@example.com** / **password** → `/admin/posts`
