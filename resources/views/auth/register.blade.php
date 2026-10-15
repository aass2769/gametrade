@extends('layouts.app')

@section('title', '회원가입')

@section('content')
    <h1>회원가입</h1>
    <form method="post" action="{{ route('register') }}" class="stack-form">
        @csrf
        <label>
            아이디
            @error('username')<p class="error">{{ $message }}</p>@enderror
            <input type="text" name="username" required maxlength="20" value="{{ old('username') }}">
        </label>
        <label>
            비밀번호
            @error('password')<p class="error">{{ $message }}</p>@enderror
            <input type="password" name="password" required maxlength="64">
        </label>
        <label>
            비밀번호 확인
            @error('password_confirm')<p class="error">{{ $message }}</p>@enderror
            <input type="password" name="password_confirm" required maxlength="64">
        </label>
        <button type="submit">가입</button>
    </form>
@endsection
