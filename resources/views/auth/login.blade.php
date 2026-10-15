@extends('layouts.app')

@section('title', '로그인')

@section('content')
    <h1>로그인</h1>
    <form method="post" action="{{ route('login') }}" class="stack-form">
        @csrf
        @error('login')
            <p class="error">{{ $message }}</p>
        @enderror
        <label>
            아이디
            <input type="text" name="username" required maxlength="20" value="{{ old('username') }}">
        </label>
        <label>
            비밀번호
            <input type="password" name="password" required maxlength="64">
        </label>
        <button type="submit">로그인</button>
    </form>
@endsection
