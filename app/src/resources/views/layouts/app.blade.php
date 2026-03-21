<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<header style="background-color: #f8f9fa; padding: 20px 20px; border-bottom: 1px solid #ddd;">
    <div style="display: flex; align-items: center; max-width: 1200px; margin: 0 auto;">
        <div style="font-size: 1.5rem; font-weight: bold; margin-right: 20px;">
            <a href="/" style="text-decoration: none; color: #333;">ロゴ</a>
        </div>
        <nav aria-label="breadcrumb" style="flex-grow: 1;">
            <ol class="breadcrumb" style="margin: 0; padding: 0; list-style: none; display: flex; gap: 5px;">
                <li class="breadcrumb-item"><a href="/challenges" style="text-decoration: none; color: #007bff;">ホーム</a></li>
                @yield('breadcrumb')
            </ol>
        </nav>
        <nav>
            <ul style="list-style: none; display: flex; gap: 15px; margin: 0; padding: 0;">
                <li>
                    @auth
                        <form method="POST" action="/logout" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: #007bff; cursor: pointer; text-decoration: underline;">ログアウト</button>
                        </form>
                    @else
                        <a href="/login" style="text-decoration: none; color: #007bff;">ログイン</a>
                    @endauth
                </li>
            </ul>
        </nav>
    </div>
</header>
    <main class="mt-4">
        @yield('content')
    </main>
    <footer class="border-top mt-5 py-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <p class="mb-0 small text-muted">&copy; {{ date('Y') }} Code Champs</p>
            </div>
        </div>
    </footer>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>
