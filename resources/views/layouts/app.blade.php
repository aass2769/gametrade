<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'GameTrade') · GameTrade</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<header class="site-header">
    <a class="logo" href="{{ route('items.index') }}">GameTrade</a>
    <nav>
        @auth
            <a href="{{ route('items.create') }}">아이템 등록</a>
            <a href="{{ route('items.mine') }}">내 판매 상품</a>
            <span class="username">{{ auth()->user()->username }}</span>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit">로그아웃</button>
            </form>
        @else
            <a href="{{ route('login') }}">로그인</a>
            <a href="{{ route('register') }}">회원가입</a>
        @endauth
    </nav>
</header>
<main>
    @if (session('status'))
        <p class="flash">{{ session('status') }}</p>
    @endif
    @yield('content')
</main>
<footer class="site-footer">
    <p>게임 아이템 판매 정보를 등록하고 조회하는 게시판입니다.</p>
</footer>
</body>
</html>
