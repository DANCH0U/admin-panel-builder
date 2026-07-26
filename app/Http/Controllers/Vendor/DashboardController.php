<?php

namespace App\Http\Controllers\Vendor;

use App\AdminPanel\Pages\Vendor\DashboardPage;
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
