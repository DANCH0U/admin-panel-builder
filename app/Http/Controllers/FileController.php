<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class FileController extends Controller
{
    public function publicDownload($filepath)
    {
        $path = public_path($filepath);

        if (!file_exists($path)) {
            abort(404);
        }
        $filename = basename($filepath);

        return response()->download($path, $filename);
    }

    public function privateDownload(Request $request, $filepath)
    {
        $path = storage_path('app/private/' . $filepath);

        if (!file_exists($path)) {
            abort(404);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $baseName = $request->query('name');

        $downloadName = $baseName
            ? $baseName . '.' . $extension
            : basename($filepath);

        return response()->download($path, $downloadName);
    }

    public function secureDownload(Request $request, $filepath)
    {
        $validated = $request->validate([
            'name' => 'string|nullable',
        ]);

        $filepath = ltrim($filepath, '/');

        $url = URL::signedRoute('secure.download', [
            'filepath' => $filepath,
            'name' => $validated['name'],
        ], now()->addMinutes(10));

        return response()->json(['url' => $url]);
    }
}
