# Admin Panel Builder

Laravel 12 + Inertia + Vue 3 admin kit. Define tables and forms in PHP; the UI renders them automatically.

Ships as a **clean kit** (no panels). Follow this guide to create a panel and a Posts CRUD.

**Login (after seed):** `admin@example.com` / `password`

**Component reference:** [SDUI_DOCUMENTATION.md](./SDUI_DOCUMENTATION.md)

<img width="1902" height="907" alt="1" src="https://github.com/user-attachments/assets/d498bc07-02d1-46de-922e-42cb34bab597" />
<img width="1901" height="910" alt="2" src="https://github.com/user-attachments/assets/78bf9f49-f1b0-4897-88a5-66657cc98c87" />
<img width="1916" height="910" alt="3" src="https://github.com/user-attachments/assets/8d6de577-6b29-420a-9586-fd2e9fbfd7cf" />

---

## 1. Install

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then:
php artisan migrate --seed
php artisan storage:link

npm install
npm run dev
php artisan serve
```

`migrate --seed` creates the admin user (avatar defaults to `/placeholder/avatar-placeholder.png`) and the `notifications` table. `storage:link` is required for public uploads. Keep `npm run dev` (or `npm run build`) running while you work.

---

## 2. Create a panel

```bash
php artisan make:admin-panel admin
```

Scaffolds Dashboard, Profile, Users CRUD, routes, and registers the panel in `AdminPanelProvider::$panels`.

### Middleware (manual)

`make:admin-panel` does **not** add middleware. Set it on the panel class:

```php
// app/AdminPanel/Panels/AdminPanel.php
$this
    ->prefix('admin')
    ->middleware(['auth', 'admin', 'panel:admin']) // drop 'admin' if is_admin is not required
    ->name('Admin Panel')
    ->logo('/placeholder/logo-placeholder.png');
```

| Middleware | Role |
|------------|------|
| `auth` | Logged in |
| `admin` | `users.is_admin` |
| `panel:{id}` | Active panel for `admin_path()` / menu |

Then open `/login` → `/admin`.

### Shell

`AdminLayout.vue`:

| Area | Contents |
|------|----------|
| **Navbar** | Brand, notifications bell, dark/light toggle, user dropdown (profile / panels / language / log out) |
| **Sidebar** | Items from `menu()` (drawer on mobile) |
| **Main** | Schema page content |

### Menu + path helpers

Never hardcode `/admin`. Use helpers (they follow the current panel from `panel:{id}` middleware):

| Helper | Example result |
|--------|----------------|
| `admin_path('posts')` | `/admin/posts` |
| `admin_home()` | `/admin` |
| `admin_menu()` | Current sidebar items |
| `admin_path('posts', 'vendor')` | Force another panel |

Outside a panel request, helpers fall back to `config('admin.default')`.

### Extra panels & switcher

```bash
php artisan make:admin-panel vendor --prefix=vendor
```

Each panel has its own class, `routes/panels/{id}.php`, and `$panels` entry. The navbar user menu lists panels that are **not** `->hidden()`. Middleware still controls access.

Login redirect uses `users.default_panel` via `admin_home_for($user)` (seeded admin → `admin`).

---

## 3. Artisan commands

| Command | Creates |
|---------|---------|
| `make:admin-panel {name}` | Panel + dashboard, profile, users CRUD |
| `make:admin-resource {name} --panel= --form --view` | Resource, controller, optional form/view |
| `make:admin-table {name} --panel=` | Resource only |
| `make:admin-page {name} --panel=` | Standalone schema page |

These commands **do not** create models or migrations. Generated CRUD controllers use **both** notification systems (toast + global inbox bell) on create / update / delete.

Auth belongs in **controllers / middleware / policies** — not on page or resource classes.

---

## 4. Example: Posts CRUD

### 4.1 Model + migration

```bash
php artisan make:model Post -m
```

Migration:

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

Model:

```php
protected $fillable = ['title', 'description', 'image', 'status', 'is_active'];

protected function casts(): array
{
    return ['is_active' => 'boolean'];
}
```

```bash
php artisan migrate
```

### 4.2 Generate resource

```bash
php artisan make:admin-resource Post --panel=admin --form --view
```

Creates `PostResource`, `PostController`, `PostFormPage`, `PostViewPage`. The controller already calls `Notify` + `AdminNotification` — customize fields next.

### 4.3 Routes + sidebar

In `routes/panels/admin.php` (command prints this):

```php
Route::post('/posts/bulk', [\App\Http\Controllers\Admin\PostController::class, 'bulk'])
    ->name('admin.posts.bulk');
Route::resource('posts', \App\Http\Controllers\Admin\PostController::class)
    ->names('admin.posts');
```

In `AdminPanel::menu()`:

```php
return PanelMenu::make()
    ->default()
    ->section('content', [
        MenuItem::link('posts', admin_path('posts'))
            ->icon('heroicons:rectangle-stack')
            ->title('Posts'),
    ])
    ->build();
```

Icons: [Iconify](https://icon-sets.iconify.design/) (`collection:icon-name`).

### 4.4 Form page — `PostFormPage`

Pages take one `$data` bag (`type` + fields). Action/method come from `type` / `id`.

```php
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
    ]);
```

```php
public function initialData(): array
{
    return [
        'title' => $this->data->title ?? '',
        'description' => $this->data->description ?? '',
        'image' => $this->data->image ?? '',
        'image_file' => null, // required so Inertia can send the upload
        'status' => $this->data->status ?? 'draft',
        'is_active' => (bool) ($this->data->is_active ?? true),
    ];
}
```

### 4.5 Controller — uploads + notifications

FileInput posts `{field}_file`. Forms send **PUT as POST + `_method`** when uploading. Use `FileUploadService` before saving.

```php
use App\AdminPanel\Notifications\Notify;
use App\Notifications\AdminNotification;
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

    $post = Post::create($validated);

    Notify::success('Post created.');
    AdminNotification::send(
        $request->user(),
        'Post "'.$post->title.'" was created.',
        title: 'Posts',
        type: 'success',
        url: admin_path('posts/'.$post->id),
    );

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
        $uploads->delete($post->image);
        $validated['image'] = $uploads->upload($request->file('image_file'), 'posts');
    }

    $post->update($validated);

    Notify::success('Post updated.');
    AdminNotification::send(
        $request->user(),
        'Post "'.$post->title.'" was updated.',
        title: 'Posts',
        type: 'info',
        url: admin_path('posts/'.$post->id),
    );

    return redirect()->route('admin.posts.index');
}

public function destroy(Request $request, Post $post, FileUploadService $uploads)
{
    $title = $post->title;
    $uploads->delete($post->image);
    $post->delete();

    Notify::success('Post deleted.');
    AdminNotification::send(
        $request->user(),
        'Post "'.$title.'" was deleted.',
        title: 'Posts',
        type: 'warning',
        url: admin_path('posts'),
    );

    return back();
}
```

`$uploads->upload()` returns a path like `posts/uuid.jpg` → public URL `/storage/posts/uuid.jpg`.

### 4.6 View + DataGrid

**View** — top actions, `KeyValue` for text, `Image` for uploads (KeyValue will not render pictures):

```php
Flex::make()->justify('between')->schema([
    Heading::make($this->data->title ?? 'Post')->level(2),
    Flex::make()->gap(2)->schema([
        Button::make('Edit')->variant('outline')->url(admin_path('posts/'.$this->data->id.'/edit')),
        Button::make('Back')->variant('secondary')->url(admin_path('posts')),
    ]),
]),
Card::make()->border()->schema([
    KeyValue::make()->entries([
        'Title' => $this->data->title ?? '—',
        'Status' => $this->data->status ?? '—',
        'Active' => ($this->data->is_active ?? false) ? 'Yes' : 'No',
    ]),
]),
Image::make($this->data->image ?? null)->label('Image');
```

**Resource** — search `title`/`description`, status tabs, image column, `is_active` filter (scaffold already includes tabs/filter; swap `name` → post fields).

---

## 5. Notifications

Two systems — generated CRUD uses **both**:

| | Toast (`Notify`) | Inbox (`AdminNotification`) |
|--|------------------|------------------------------|
| Scope | Per request | **Global per user** (all panels share one inbox) |
| Storage | Session flash | Laravel `notifications` table |
| Class | `App\AdminPanel\Notifications\Notify` | `App\Notifications\AdminNotification` |
| UI | Sonner toast | Navbar bell + badge |
| Typical use | Immediate feedback after save | Persistent history / click-through |

Inbox is **not** generated per panel. It ships with the kit:

| Piece | Path |
|-------|------|
| Notification class | `app/Notifications/AdminNotification.php` |
| API controller | `app/Http/Controllers/NotificationController.php` |
| Routes | `/notifications` in `routes/web.php` (auth) |
| Migration | `*_create_notifications_table.php` |
| UI | Navbar bell in `AdminLayout` |

### How to use

```php
use App\AdminPanel\Notifications\Notify;
use App\Notifications\AdminNotification;

// Toast — disappears after the next page load
Notify::success('Saved');
Notify::danger('Failed')->title('Error')->duration(8000);

// Inbox — persists until the user reads/deletes it (visible in every panel)
AdminNotification::send(
    $request->user(),
    'A new post needs review.',
    title: 'Posts',
    type: 'info', // success | info | warning | danger
    url: admin_path('posts'), // optional click-through
);
```

Bell API (auth middleware):

| Method | Path | Action |
|--------|------|--------|
| `GET` | `/notifications` | List latest + unread count |
| `POST` | `/notifications/{id}/read` | Mark one as read |
| `POST` | `/notifications/read-all` | Mark all as read |
| `DELETE` | `/notifications/{id}` | Delete one |

Unread count is shared to Inertia as `auth.unread_notifications`. The `User` model must use Laravel’s `Notifiable` trait (default).
---

## 6. Languages

Translate in PHP with `__('admin.key')`. Register locales in `config/admin.php` → `languages` / `default_locale`, add files under `resources/lang/{locale}/`, then use `__()` in menus, pages, and controllers. The navbar language list switches locale via session/cookie (`SetLocale`).

```php
MenuItem::link('posts', admin_path('posts'))
    ->title(__('admin.posts'));
```

---

## 7. Theme

| File | Role |
|------|------|
| `resources/css/theme/tokens.css` | `--radius`, light colors |
| `resources/css/theme/dark.css` | Dark overrides |
| `resources/css/theme/base.css` | Base styles |
| `resources/css/theme/utilities.css` | `.admin-surface` |

Dark mode: navbar toggle → `localStorage.theme` → `dark` class on `<html>`. Loader delay: `config('admin.ui.loading_delay_ms')` (default 200).

---

## Quick reference

**Useful inputs:** `TextInput`, `Textarea`, `FileInput`, `NumberInput`, `Select`, `Toggle`, `Checkbox`, `DateTimeInput`, `MultiSelect`, `TagsInput`

**Useful columns:** every column shares `->label()`, `->sortable()`, `->toggleable()`, `->hidden()`, `->exportable(false)`, `->prefixText()` / `->suffixText()`, `->transform(fn ($value, $row) => …)`

| Column | Extra options |
|--------|---------------|
| `TextColumn` | `->maxLength(10)` — clips the cell, full text stays in the tooltip and CSV export |
| `ImageColumn` | `->rounded()` |
| `BadgeColumn` | `->colors(['success' => 'published', 'warning' => 'draft'])` |
| `BooleanColumn` | `->labels('On', 'Off')` (defaults to `Yes` / `No`) |
| `JsonColumn` | `->limit(3)`, `->pretty()` |

**Filters:** `SelectFilter`/`RelationFilter` (`->options()`), `BooleanFilter` (`->labels()`), `MultiSelectFilter` (`->options()`), `DateRangeFilter`, `NumericRangeFilter` (`->min()` / `->max()` bound the inputs). All render in the filter popover and round-trip through the URL.

**Actions:** `->icon('lucide:eye')` uses any Iconify name (otherwise an icon is guessed from the action name), and `Action::group('More', [...])` becomes a submenu.

**Layout / view:** `Card`, `Grid`, `Flex`, `Section`, `Tabs`, `Heading`, `Text`, `KeyValue`, `Chart`, `Space`, `Image`

**Charts:** `Chart::make()->type('area'|'line'|'bar'|'column'|'pie'|'donut')` — `bar` horizontal, `column` vertical. Or `->api('/path')` for JSON `{ series, categories?, labels? }`.

**Mobile:** `Button::make('Save')->submit()->showOnBottomBar()` moves the button to a fixed bottom bar on small screens.

Full options: [SDUI_DOCUMENTATION.md](./SDUI_DOCUMENTATION.md).

---

## Checklist

```bash
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate --seed && php artisan storage:link && npm install && npm run dev

php artisan make:admin-panel admin
# Edit AdminPanel middleware: ['auth', 'admin', 'panel:admin']

php artisan make:model Post -m && php artisan migrate
php artisan make:admin-resource Post --panel=admin --form --view
# Add routes + menu, customize form fields / controller validation
```

Login: **admin@example.com** / **password** → `/admin` → `/admin/posts`
