@extends('layouts.app')

@section('title', '페이지를 찾을 수 없습니다')

@section('content')
    <div class="empty-state panel">
        <h1>404</h1>
        <p>{{ $exception->getMessage() !== '' ? $exception->getMessage() : '페이지를 찾을 수 없습니다.' }}</p>
        <p><a class="button button-primary" href="{{ route('items.index') }}">목록으로</a></p>
    </div>
@endsection
