<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanel\Engine\DataGridEngine;
use App\AdminPanel\Notifications\Notify;
use App\AdminPanel\Resources\Admin\PostResource;
use App\AdminPanel\Pages\Admin\PostFormPage;
use App\AdminPanel\Pages\Admin\PostViewPage;
use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index(Request $request, DataGridEngine $engine)
    {
        $data = $engine->handle(new PostResource(), $request);

        return Inertia::render('Admin/ResourceIndex', [
            'resource' => $data,
            'title' => 'Posts',
            'description' => null,
            'createUrl' => admin_path('posts/create'),
            'createLabel' => 'Add Post',
        ]);
    }

    public function bulk(Request $request, DataGridEngine $engine)
    {
        return $engine->runBulkAction(new PostResource(), $request);
    }

    public function create()
    {
        $page = new PostFormPage(
            action: admin_path('posts'),
            method: 'POST',
            pageTitle: 'Create Post',
        );

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => $page->initialData(),
        ]));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Post::create($validated);
        Notify::success('Post created.');

        return redirect()->route('posts.index');
    }

    public function edit(Post $post)
    {
        $page = new PostFormPage(
            action: admin_path('posts/'.$post->getKey()),
            method: 'PUT',
            pageTitle: 'Edit Post',
            post: $post,
        );

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => $page->initialData(),
        ]));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $post->update($validated);
        Notify::success('Post updated.');

        return redirect()->route('posts.index');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        Notify::success('Post deleted.');

        return back();
    }
    public function show(Post $post)
    {
        $page = new PostViewPage($post);

        return Inertia::render('Admin/SchemaPage', $page->toInertia());
    }
}
