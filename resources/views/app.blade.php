<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia> TITLE </title>

    <link rel="icon" href="" type="image/png">

    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta property="og:title" content="">
    <meta property="og:description" content="">
    <meta property="og:image" content="">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @php
        $adminLanguage = admin_language();
        $adminFont = $adminLanguage['font'] ?? 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400..700;1,400..700&display=swap';
        $adminFontFamily = admin_font_family(null, $adminLanguage);
    @endphp
    <link id="admin-lang-font" href="{{ $adminFont }}" rel="stylesheet">
    <style>
        :root {
            --admin-font-family: '{{ $adminFontFamily }}';
        }
    </style>

    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead

</head>

<body class="font-sans antialiased">
    <div id="ls-app">
        @inertia
    </div>
</body>

</html>
