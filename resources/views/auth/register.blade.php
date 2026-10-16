@extends('layouts.app')

@section('title', '회원가입')

@section('content')
    <div class="auth-shell">
        <section class="panel auth-panel">
            <p class="eyebrow">Create account</p>
            <h1>회원가입</h1>
            <p class="lede">아이디와 비밀번호만으로 바로 시작할 수 있습니다.</p>
            <form method="post" action="{{ route('register') }}" class="stack-form">
                @csrf
                <label>
                    아이디
                    @error('username')<p class="error">{{ $message }}</p>@enderror
                    <input type="text" name="username" required maxlength="20" value="{{ old('username') }}" autocomplete="username">
                </label>
                <label>
                    비밀번호
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                    <input type="password" name="password" required maxlength="64" autocomplete="new-password">
                </label>
                <label>
                    비밀번호 확인
                    @error('password_confirm')<p class="error">{{ $message }}</p>@enderror
                    <input type="password" name="password_confirm" required maxlength="64" autocomplete="new-password">
                </label>
                <button type="submit" class="button button-primary">가입</button>
            </form>
            <p class="auth-foot">이미 계정이 있다면 <a href="{{ route('login') }}">로그인</a></p>
        </section>
    </div>
@endsection
