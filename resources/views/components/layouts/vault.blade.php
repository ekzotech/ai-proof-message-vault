<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'AI-Proof Message Vault' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body>
        <header class="site-header">
            <div class="page-width site-header__inner">
                <a class="wordmark" href="{{ url('/') }}" lang="en">Message Vault</a>
            </div>
        </header>

        <main class="page-width" id="content">{{ $slot }}</main>

        @livewireScripts
    </body>
</html>
