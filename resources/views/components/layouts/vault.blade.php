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
                <a class="wordmark" href="{{ route('home') }}" lang="en">Message Vault</a>
                <nav class="site-header__nav" aria-label="Аккаунт">
                    @guest
                        <a class="site-header__link" href="{{ route('register') }}">Регистрация</a>
                        <a class="site-header__link" href="{{ route('login') }}">Войти</a>
                    @else
                        <a class="site-header__link" href="{{ route('dashboard') }}">Кабинет</a>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="site-header__link" type="submit">Выйти</button>
                        </form>
                    @endguest
                </nav>
            </div>
        </header>

        <main class="page-width" id="content">{{ $slot }}</main>

        @livewireScripts
    </body>
</html>
