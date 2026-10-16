@extends('layouts.app')

@section('title', '권한 없음')

@section('content')
    <div class="empty-state panel">
        <h1>403</h1>
        <p>{{ $exception->getMessage() }}</p>
        <p><a class="button button-primary" href="{{ route('items.index') }}">목록으로</a></p>
    </div>
@endsection