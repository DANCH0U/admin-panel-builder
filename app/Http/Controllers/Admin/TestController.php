<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanel\Notifications\Notify;
use App\AdminPanel\Pages\Admin\TestPage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestController extends Controller
{
    public function show()
    {
        $page = new TestPage;

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => $page->initialData(),
        ]));
    }

    public function storeFields(Request $request)
    {
        (new TestPage)->authorizeOrFail();

        $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        Notify::success('Fields form submitted (demo — not saved).');

        return back();
    }

    public function storeSettings(Request $request)
    {
        (new TestPage)->authorizeOrFail();

        $request->validate([
            'density' => ['nullable', 'string', 'in:comfortable,compact'],
            'accent' => ['nullable', 'string', 'in:blue,teal,amber'],
        ]);

        Notify::success('Settings form submitted (demo — not saved).');

        return back();
    }

    public function storeFeedback(Request $request)
    {
        (new TestPage)->authorizeOrFail();

        $request->validate([
            'rating' => ['required', 'string', 'in:1,2,3,4,5'],
            'message' => ['required', 'string', 'max:2000'],
            'contact' => ['nullable', 'email', 'max:255'],
        ]);

        Notify::success('Thanks for the feedback (demo — not saved).');

        return back();
    }
}
