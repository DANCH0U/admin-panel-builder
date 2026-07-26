<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanel\Pages\Admin\DashboardPage;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $page = new DashboardPage();

        return Inertia::render('Admin/SchemaPage', $page->toInertia());
    }
}
