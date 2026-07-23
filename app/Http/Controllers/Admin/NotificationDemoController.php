<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanel\Notifications\Notify;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationDemoController extends Controller
{
    public function flash(Request $request, string $type)
    {
        $withAction = $request->boolean('action');

        match ($type) {
            'success' => $withAction
                ? Notify::success('Record saved successfully.')->action('View tests', admin_path('tests'))
                : Notify::success('Operation completed successfully.'),
            'info' => Notify::info('This is an informational message.'),
            'warning' => Notify::warning('Please review this warning before continuing.'),
            'danger' => Notify::danger('Something went wrong. Please try again.'),
            default => Notify::danger('Unknown notification type.'),
        };

        return back();
    }
}
