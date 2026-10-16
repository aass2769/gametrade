<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'GameTrade') · GameTrade</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="page">
    <header class="site-header">
        <div class="shell header-bar">
            <a class="logo" href="{{ route('items.index') }}">
                <img class="logo-mark" src="{{ asset('images/logo.svg') }}" width="32" height="32" alt="">
                <span class="logo-text">GameTrade</span>
            </a>
            <nav class="site-nav">
                @auth
                    <a class="nav-link" href="{{ route('items.create') }}">아이템 등록</a>
                    <a class="nav-link" href="{{ route('items.mine') }}">내 판매 상품</a>
                    <span class="username">{{ auth()->user()->username }}</span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="button button-ghost">로그아웃</button>
                    </form>
                @else
                    <a class="nav-link" href="{{ route('login') }}">로그인</a>
                    <a class="button button-primary" href="{{ route('register') }}">회원가입</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="shell main-panel">
        @if (session('status'))
            <p class="flash">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="shell">
            <p>게임 아이템 판매 정보를 등록하고 조회하는 게시판입니다.</p>
        </div>
    </footer>
</div>
</body>
</html>
