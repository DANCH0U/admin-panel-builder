# SDUI documentation — Schema & DataGrid UI

PHP defines the UI; Laravel serializes it; Inertia + Vue render it.

- **Pages / forms:** `App\AdminPanel\Schema\*` → `Admin/SchemaPage` + `SchemaRenderer`
- **Tables:** `App\AdminPanel\Tables\*` → `Admin/ResourceIndex` + `DataTable`

Use the public namespaces only (`Schema`, `Tables`). Prefer `admin_path()` for URLs (current panel).

> Tutorial (install, panels, Posts CRUD): see **[README.md](./README.md)**.

---

## Generate

```bash
php artisan make:admin-page Reports --panel=admin
php artisan make:admin-resource Post --panel=admin --form --view
php artisan make:admin-table Product --panel=admin --model=App\\Models\\Product
```

| Command | Creates |
|---------|---------|
| `make:admin-page` | `Pages/{Panel}/…Page.php` → render with `Admin/SchemaPage` |
| `make:admin-resource` | Resource + controller (+ optional form/view pages) |
| `make:admin-table` | Resource only |

All require `--panel=` (registry id or URL prefix). Generators do **not** create models/migrations.

---

## Shared component API

Every schema component supports:

| Method | Purpose |
|--------|---------|
| `->label('…')` | Field / block title |
| `->helpText('…')` | Secondary help under the label |
| `->width('…')` | CSS width hint |
| `->columnSpan(n)` | Span inside a `Grid` |
| `->visibleWhen($field, $value, $op = '=')` | Show when form field matches |
| `->disabledWhen($field, $value, $op = '=')` | Disable when form field matches |

Form fields (via `HasFieldOptions`) also support:

`->placeholder()`, `->required()`, `->disabled()`, `->readonly()`, `->hint()`, `->size()`, `->default()`.

Import from `App\AdminPanel\Schema\{…}` (public aliases).

---

## Layout components

Containers that nest other components with `->schema([...])`.

### `Form`

Wraps fields and submits via Inertia (`forceFormData`). **PUT/PATCH** are sent as **POST + `_method`** so file uploads work with PHP.

```php
Form::make()
    ->action(admin_path('posts'))
    ->method('POST') // or PUT / PATCH / DELETE
    ->border()       // optional bordered surface
    ->schema([ /* fields + Button::make('Save')->submit() */ ]);
```

| Method | Notes |
|--------|--------|
| `->action($url)` | Submit URL |
| `->method('POST'\|'PUT'\|'PATCH'\|'DELETE')` | HTTP verb (spoofed when needed) |
| `->border()` | Bordered card chrome around the form |

### `Card`

Grouped content surface.

```php
Card::make()->border()->label('Post')->helpText('Basics')->schema([…]);
```

| Method | Notes |
|--------|--------|
| `->border()` | Show border + padding (off by default) |
| `->label()` / `->helpText()` | Optional header |

### `Section`

Titled block; optional fold.

```php
Section::make('Details')
    ->description('Basic information.')
    ->foldable()
    ->schema([…]);
```

### `Grid`

Responsive column grid. Children can use `->columnSpan(n)`.

```php
Grid::make(2)->schema([
    TextInput::make('first')->label('First'),
    TextInput::make('last')->label('Last'),
]);
```

`Grid::make(12)` = 12-column grid (default).

### `Flex`

Row/column flex layout (headers, button rows).

```php
Flex::make()
    ->justify('between')  // start | center | end | between | around | evenly
    ->align('center')     // start | center | end | stretch | baseline
    ->direction('row')    // row | column
    ->gap(2)
    ->wrap()
    ->schema([…]);
```

### `Tabs` / `Tab`

In-page tabs (form sections), not DataGrid filter tabs.

```php
Tabs::make()->schema([
    Tab::make('General')->schema([…]),
    Tab::make('Media')->schema([…]),
]);
```

---

## View / display components (read-only)

Use on show pages (no form binding required).

### `Heading`

```php
Heading::make('Post title')->level(2); // 1–6
```

### `Text`

```php
Text::make('Supporting copy')->variant('body');
// variants: body | subdued | caption
```

### `KeyValue`

String → string (or nested JSON) list. **Not** for images — use `Image`.

```php
KeyValue::make()->label('Details')->entries([
    'Status' => $record->status,
    'Active' => $record->is_active ? 'Yes' : 'No',
    'Date' => $record->created_at?->format('M d, Y'),
    'Meta' => $record->meta, // arrays/objects → pretty JSON
]);
```

### `Image`

Dedicated image block. Storage paths become public URLs via `FileUploadService`.

```php
Image::make($record->image)->label('Cover')->rounded();
```

### `Button`

Submit or navigate.

```php
Button::make('Save')->submit();
Button::make('Edit')->variant('outline')->url(admin_path('posts/'.$id.'/edit'));
Button::make('Back')->variant('secondary')->url(admin_path('posts'));
Button::make('Go back')->back(); // browser history
```

| Method | Notes |
|--------|--------|
| `->submit()` | `type="submit"` inside a `Form` |
| `->url($path)` | Inertia visit |
| `->back()` | `history.back()` |
| `->variant('primary'\|'secondary'\|'outline'\|'destructive'\|'ghost'\|…)` | shadcn variants |
| `->icon('…')` | Optional icon name (passed to UI) |
| `->type('button'\|'submit')` | Native button type |

**View page pattern** (header + card + image):

```php
use App\AdminPanel\Schema\{Button, Card, Flex, Heading, Image, KeyValue, Text};

return [
    Flex::make()->justify('between')->schema([
        Heading::make($record->title)->level(2),
        Flex::make()->gap(2)->schema([
            Button::make('Edit')->variant('outline')->url(admin_path('posts/'.$record->getKey().'/edit')),
            Button::make('Back')->variant('secondary')->url(admin_path('posts')),
        ]),
    ]),
    Card::make()->border()->schema([
        Text::make($record->description ?? '')->variant('body'),
        KeyValue::make()->entries([/* … */]),
    ]),
    Image::make($record->image)->label('Image'),
];
```

---

## Form field components

### `TextInput`

```php
TextInput::make('title')->label('Title')->required();
TextInput::make('email')->email();
TextInput::make('password')->password();
TextInput::make('website')->url();
TextInput::make('phone')->tel();
TextInput::make('custom')->type('search');
```

### `Textarea`

```php
Textarea::make('description')->label('Description')->rows(5);
```

### `NumberInput`

```php
NumberInput::make('price')->min(0)->max(9999)->step(0.01)->controls();
```

### `Select`

```php
Select::make('status')
    ->label('Status')
    ->options(['draft' => 'Draft', 'published' => 'Published'])
    ->placeholder('Choose…')
    ->searchable()
    ->required();

Select::make('city_id')
    ->optionsApi(admin_path('api/cities'), 'country_id'); // dependent options
```

### `MultiSelect`

```php
MultiSelect::make('tags')->label('Tags')->options([
    'news' => 'News',
    'ops' => 'Ops',
]);
```

### `Checkbox`

```php
Checkbox::make('agree')->label('I agree');
```

### `Toggle`

Boolean switch (uses `modelValue`).

```php
Toggle::make('is_active')->label('Active');
```

### `DateTimeInput`

```php
DateTimeInput::make('published_at')->withTime();
DateTimeInput::make('due_on')->dateOnly();
DateTimeInput::make('starts_at')->displayFormat('MMM D, YYYY HH:mm');
```

### `FileInput`

Uploads as `{name}_file` (e.g. `image_file`). Include that key in `initialData()` as `null` so Inertia keeps the File. Preview resolves `/storage/…` for relative paths.

```php
FileInput::make('image')->label('Image')->image();
FileInput::make('doc')->accept('.pdf')->directory('docs')->maxSizeKb(2048);
```

| Method | Notes |
|--------|--------|
| `->image()` | Image picker + preview |
| `->accept('image/*,.pdf')` | MIME / extension filter |
| `->multiple()` | Multi-file (when supported by UI) |
| `->directory('posts')` | Hint for upload folder |
| `->disk('public')` | Storage disk hint |
| `->maxSizeKb(4096)` | Max size hint |

Controller side: validate `image_file`, upload with `FileUploadService`, store relative path on the model.

### `ListInput`

Simple list of strings.

```php
ListInput::make('keywords')->label('Keywords');
```

### `JsonInput`

Structured repeating key/value (or schema-driven) JSON editor UI.

```php
JsonInput::make('meta')->label('Metadata');
```

### `JsonCodeInput`

Raw JSON textarea with format helper.

```php
JsonCodeInput::make('payload')->label('Payload')->placeholder('{}');
```

---

## Component cheat sheet (pages)

| PHP class | Serialized `type` | Role |
|-----------|-------------------|------|
| `Form` | `form` | Submit wrapper |
| `Card` | `card` | Surface |
| `Section` | `section` | Titled / foldable block |
| `Grid` | `grid` | Columns |
| `Flex` | `flex` | Flex row/column |
| `Tabs` / `Tab` | `tabs` / tab children | In-page tabs |
| `Heading` | `ui-heading` | Title |
| `Text` | `ui-text` | Paragraph |
| `Image` | `ui-image` | Image block |
| `Button` | `ui-button` | Action |
| `KeyValue` | `key-value` | Read-only pairs |
| `TextInput` | `text-input` | Text field |
| `Textarea` | `textarea` | Multiline |
| `NumberInput` | `number-input` | Number |
| `Select` | `select-input` | Select |
| `MultiSelect` | `multi-select` | Multi checkbox select |
| `Checkbox` | `checkbox` | Checkbox |
| `Toggle` | `toggle` | Switch |
| `DateTimeInput` | `datetime-input` | Date / datetime |
| `FileInput` | `file-input` | Upload |
| `ListInput` | `list-input` | String list |
| `JsonInput` | `json-input` | Structured JSON |
| `JsonCodeInput` | `json-code` | Raw JSON |

Vue mapping: `resources/js/components/Admin/Schema/registry.ts`.

---

## DataGrid (tables)

Define in a `BaseResource` `schema()` array. Render with `DataGridEngine` → `Admin/ResourceIndex`.

### Columns

| Class | Notes |
|-------|--------|
| `TextColumn` | Default text; `->sortable()`, `->toggleable()`, `->transform()` |
| `BadgeColumn` | `->colors(['success' => 'published', 'warning' => 'draft'])` |
| `BooleanColumn` | Yes/no style |
| `ImageColumn` | Thumb; `->rounded()`; auto public URL |
| `JsonColumn` | JSON display |

Shared: `->label()`, `->hidden()`, `->sortable()`, `->toggleable()`, `->prefixText()`, `->suffixText()`, `->using($relation)`.

### Filters / search / tabs

```php
'search_columns' => [
    Search::column('title')->weight(3),
],
'tabs' => Tabs::make([
    Tab::make('all'),
    Tab::make('draft')->query(fn ($q) => $q->where('status', 'draft'))->color('warning'),
]),
'filters' => [
    SelectFilter::make('is_active')->label('Active')->options([
        '1' => 'Active',
        '0' => 'Inactive',
    ]),
],
```

### Actions

```php
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
```

Toolbar **Filters** and **Columns** are icon-only. Column visibility uses `->toggleable()`.

---

## Rendering from a controller

**Schema page**

```php
return Inertia::render('Admin/SchemaPage', $page->toInertia([
    'initialData' => $page->initialData(), // forms
]));
```

**Resource index**

```php
return Inertia::render('Admin/ResourceIndex', [
    'resource' => $engine->handle(new PostResource(), $request),
    'title' => 'Posts',
    'createUrl' => admin_path('posts/create'),
    'createLabel' => 'Add Post',
]);
```

Always call `authorize()` / `authorizeOrFail()` on pages and resources.

---

## Path helpers

Current panel is set by `panel:{id}` middleware.

```php
admin_path('posts');           // "/admin/posts" on the admin panel
admin_path('posts', 'vendor'); // force another panel
admin_home();
admin_prefix();
```

Frontend: `useAdminConfig().adminPath('posts')`.

---

## Extending

1. Add PHP class under `app/AdminPanel/Schema/…` (+ public alias if needed).
2. Add Vue node under `resources/js/components/Admin/Schema/nodes/`.
3. Register the serialized `type` in `registry.ts`.

Keep schemas JSON-safe: resolve queries/closures before sending to Inertia.

UI kit: **shadcn/vue** in `resources/js/components/ui`. Icons in menus: **Iconify** (`collection:name`).
