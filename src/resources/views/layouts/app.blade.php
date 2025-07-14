<!DOCTYPE html>
<html lang='ja'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>COACHTECH フリマ</title>
    <meta name='csrf-token' content='{{ csrf_token() }}'>

    <!-- Styles -->
    <link rel='stylesheet' href='{{ asset("css/style.css") }}'>

    <!-- Scripts -->
    <script src='{{ asset("js/app.js") }}' defer></script>
</head>
<body>
    <header class='header'>
        <div class='header__inner'>
            <a href='{{ route("items.index") }}' class='header__logo'>
                <img src='{{ asset("images/logo.svg") }}' alt='COACHTECH' class='header__logo-image'>
            </a>

            <form action='{{ route("items.index") }}' method='GET' class='header__search'>
                <input type='text' name='keyword' class='header__search-input' placeholder='なにをお探しですか？' value='{{ $keyword ?? "" }}'>
            </form>

            <nav class='header__nav'>
                @if (Auth::check())
                <form method='POST' action='{{ route("logout") }}' class='header__nav-form'>
                    @csrf
                    <button type='submit' class='header__nav-link header__nav-link--button'>ログアウト</button>
                </form>
                <a href='{{ route("profile.show") }}' class='header__nav-link'>マイページ</a>
                <a href='{{ route("items.create") }}' class='header__nav-button'>出品</a>
                @else
                <!-- <a href='{{ route('register') }}' class='header__nav-link'>会員登録</a> -->
                <a href='{{ route("login") }}' class='header__nav-link'>ログイン</a>
                @endif
            </nav>

            <button class='header__menu-button' type='button'>
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class='header__mobile-menu'>
                <nav class='header__mobile-nav'>
                    <div class='header__search'>
                        <form action='' method='GET'>
                            <input type='text' name='keyword' placeholder='なにをお探しですか？'>
                        </form>
                    </div>
                    @auth
                        <a href='' class='header__nav-link'>マイページ</a>
                        <form action='{{ route("logout") }}' method='POST'>
                            @csrf
                            <button type='submit' class='header__nav-button'>ログアウト</button>
                        </form>
                        <a href='' class='header__nav-button header__nav-button'>出品する</a>
                    @else
                        <a href='{{ route("register") }}' class='header__nav-link'>会員登録</a>
                        <a href='{{ route("login") }}' class='header__nav-link'>ログイン</a>
                        <a href='' class='header__nav-button header__nav-button'>出品する</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main class='main'>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>