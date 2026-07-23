<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanel\Notifications\Notify;
use App\AdminPanel\Pages\SettingsPage;
use App\Http\Controllers\Controller;
use App\Models\PanelSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function edit()
    {
        $page = new SettingsPage();

        return Inertia::render('Admin/SchemaPage', $page->toInertia([
            'initialData' => $page->initialData(),
        ]));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:120'],
            'navbar_title' => ['nullable', 'string', 'max:120'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'show_theme_toggle' => ['sometimes', 'boolean'],
        ]);

        $settings = PanelSetting::forPanel();
        $settings->fill([
            'app_name' => $validated['app_name'],
            'navbar_title' => $validated['navbar_title'] ?: null,
            'logo_url' => $validated['logo_url'] ?: null,
            'show_theme_toggle' => $request->boolean('show_theme_toggle'),
        ]);
        $settings->save();

        Notify::success('Settings saved.');

        return back();
    }
}
