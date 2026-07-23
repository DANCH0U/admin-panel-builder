<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanel\Engine\DataGridEngine;
use App\AdminPanel\Notifications\Notify;
use App\AdminPanel\Pages\TestFormPage;
use App\AdminPanel\Pages\TestViewPage;
use App\AdminPanel\Resources\TestResource;
use App\Domains\Test\Models\Test;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TestController extends Controller
{
    public function index(Request $request, DataGridEngine $engine)
    {
        $resource = new TestResource();
        $data = $engine->handle($resource, $request);

        return Inertia::render('Admin/Tests/Index', [
            'resource' => $data,
        ]);
    }

    public function bulk(Request $request, DataGridEngine $engine)
    {
        return $engine->runBulkAction(new TestResource(), $request);
    }

    public function create()
    {
        $page = new TestFormPage(
            action: admin_path('tests'),
            method: 'POST',
            pageTitle: 'Create test',
        );

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => $page->initialData(),
        ]));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTest($request);
        Test::create($validated);
        Notify::success('Test created successfully.')->action('View all', admin_path('tests'));

        return redirect()->route('tests.index');
    }

    public function show(Test $test)
    {
        $page = new TestViewPage($test);

        return Inertia::render('Admin/SchemaPage', $page->toInertia());
    }

    public function edit(Test $test)
    {
        $page = new TestFormPage(
            action: admin_path("tests/{$test->uuid}"),
            method: 'PUT',
            pageTitle: 'Edit test: ' . $test->name,
            test: $test,
        );

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => $page->initialData(),
        ]));
    }

    public function update(Request $request, Test $test)
    {
        $validated = $this->validateTest($request);
        $test->update($validated);
        Notify::success('Test updated successfully.');

        return redirect()->route('tests.show', $test);
    }

    public function destroy(Test $test)
    {
        $test->delete();
        Notify::success('Test deleted successfully.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTest(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'draft', 'archived'])],
            'category' => ['nullable', Rule::in(['general', 'billing', 'support', 'product'])],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', Rule::in(['alpha', 'beta', 'urgent', 'internal', 'demo'])],
            'metadata' => ['nullable', 'array'],
            'metadata.*.title' => ['nullable', 'string', 'max:255'],
            'metadata.*.kind' => ['nullable', 'string', 'max:50'],
            'metadata.*.body' => ['nullable', 'string'],
            'metadata.*.image' => ['nullable', 'string', 'max:500'],
            'metadata.*.enabled' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'cover_image_file' => ['nullable', 'image', 'max:2048'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_public'] = $request->boolean('is_public');
        $validated['tags'] = array_values($validated['tags'] ?? []);
        $validated['checklist'] = array_values(array_filter($validated['checklist'] ?? [], fn ($v) => filled($v)));
        $validated['metadata'] = $this->normalizeMetadata($request->input('metadata'));

        if ($request->hasFile('cover_image_file')) {
            $path = $request->file('cover_image_file')->store('tests/covers', 'public');
            $validated['cover_image'] = '/storage/' . $path;
        }

        // Nested block image uploads: metadata[i][image_file]
        foreach ($request->file('metadata', []) as $index => $files) {
            if (!is_array($files) || empty($files['image_file'])) {
                continue;
            }
            $path = $files['image_file']->store('tests/blocks', 'public');
            $validated['metadata'][$index]['image'] = '/storage/' . $path;
        }

        unset($validated['cover_image_file'], $validated['confirm_review']);

        if (($validated['published_at'] ?? null) === '') {
            $validated['published_at'] = null;
        }

        return $validated;
    }

    /**
     * @param  mixed  $raw
     * @return array<int, array<string, mixed>>
     */
    private function normalizeMetadata(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($raw)) {
            return [];
        }

        if (!array_is_list($raw)) {
            $raw = [$raw];
        }

        return array_values(array_map(function ($row) {
            $row = is_array($row) ? $row : [];

            return [
                'title' => (string) ($row['title'] ?? ''),
                'kind' => (string) ($row['kind'] ?? 'note'),
                'body' => (string) ($row['body'] ?? ''),
                'image' => (string) ($row['image'] ?? ''),
                'enabled' => filter_var($row['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }, $raw));
    }
}
