<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanel\Pages\DemoPageBuilder;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DemoPageController extends Controller
{
    public function show()
    {
        $page = new DemoPageBuilder();

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'theme' => 'light',
                'newsletter' => false,
            ],
        ]));
    }

    public function save()
    {
        return back()->with('success', 'Page configuration saved!');
    }
}
