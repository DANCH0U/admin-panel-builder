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
use App\Services\FileUploadService;

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

    public function store(Request $request, FileUploadService $uploads)
    {
        (new PostFormPage(
            action: admin_path('posts'),
            method: 'POST',
        ))->authorizeOrFail();

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

    public function update(Request $request, Post $post, FileUploadService $uploads)
    {
        (new PostFormPage(
            action: admin_path('posts/'.$post->getKey()),
            method: 'PUT',
            post: $post,
        ))->authorizeOrFail();
    
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
    
        return redirect()->route('posts.index');
    }

    public function destroy(Post $post, FileUploadService $uploads)
    {
        (new PostResource())->authorizeOrFail();
        $uploads->delete($post->image);
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
