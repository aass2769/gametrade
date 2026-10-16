@extends('layouts.app')

@section('title', '로그인')

@section('content')
    <div class="auth-shell">
        <section class="panel auth-panel">
            <p class="eyebrow">Welcome back</p>
            <h1>로그인</h1>
            <p class="lede">판매 등록과 내 상품 관리를 위해 로그인해 주세요.</p>
            <form method="post" action="{{ route('login') }}" class="stack-form">
                @csrf
                @error('login')
                    <p class="error">{{ $message }}</p>
                @enderror
                <label>
                    아이디
                    <input type="text" name="username" required maxlength="20" value="{{ old('username') }}" autocomplete="username">
                </label>
                <label>
                    비밀번호
                    <input type="password" name="password" required maxlength="64" autocomplete="current-password">
                </label>
                <button type="submit" class="button button-primary">로그인</button>
            </form>
            <p class="auth-foot">계정이 없다면 <a href="{{ route('register') }}">회원가입</a></p>
        </section>
    </div>
@endsection
