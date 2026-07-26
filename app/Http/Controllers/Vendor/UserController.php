<?php

namespace App\Http\Controllers\Vendor;

use App\AdminPanel\Engine\DataGridEngine;
use App\AdminPanel\Notifications\Notify;
use App\AdminPanel\Pages\Vendor\UserFormPage;
use App\AdminPanel\Pages\Vendor\UserViewPage;
use App\AdminPanel\Resources\Vendor\UserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request, DataGridEngine $engine)
    {
        $resource = new UserResource();

        return Inertia::render(
            'Admin/ResourceIndex',
            $resource->toIndexProps($engine->handle($resource, $request)),
        );
    }

    public function bulk(Request $request, DataGridEngine $engine)
    {
        return $engine->runBulkAction(new UserResource(), $request);
    }

    public function create()
    {
        $page = new UserFormPage(
            action: admin_path('users'),
            method: 'POST',
            pageTitle: 'Create user',
        );

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => $page->initialData(),
        ]));
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);
        User::create($validated);
        Notify::success('User created.');

        return redirect()->route('vendor.users.index');
    }

    public function show(User $user)
    {
        $page = new UserViewPage($user);

        return Inertia::render('Admin/SchemaPage', $page->toInertia());
    }

    public function edit(User $user)
    {
        $page = new UserFormPage(
            action: admin_path("users/{$user->getKey()}"),
            method: 'PUT',
            pageTitle: 'Edit user: '.$user->name,
            user: $user,
        );

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => $page->initialData(),
        ]));
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validateUser($request, $user);
        $user->update($validated);
        Notify::success('User updated.');

        return redirect()->route('vendor.users.show', $user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        Notify::success('User deleted.');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUser(Request $request, ?User $user = null): array
    {
        $panelIds = array_keys(\App\AdminPanel\PanelRegistry::all());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::defaults()],
            'is_admin' => ['sometimes', 'boolean'],
            'default_panel' => ['nullable', 'string', Rule::in($panelIds)],
        ]);

        $validated['is_admin'] = $request->boolean('is_admin');

        if (($validated['default_panel'] ?? '') === '') {
            $validated['default_panel'] = null;
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        return $validated;
    }
}
