<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bands API')</title>
    <meta name="description" content="Painel de demonstração da API de bandas construída com Laravel 13 e PHP 8.5.">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

    <header class="site-header">
        <div class="shell site-header__inner">
            <p class="brand">DIO <span>Bands API</span></p>
            <nav aria-label="Navegação principal">
                <ul class="nav-list">
                    <li><a href="{{ route('dashboard') }}">Painel</a></li>
                    <li><a href="{{ route('bands.index') }}">API v1</a></li>
                    <li><a href="{{ url('/up') }}">Saúde</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="conteudo" class="shell">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="shell">
            <p>Laravel 13 · PHP 8.5.13 · SQLite · Caddy</p>
        </div>
    </footer>
</body>
</html>
