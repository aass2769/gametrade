@extends('layouts.app')

@section('title', $title ?? '요청 오류')

@section('content')
    <div class="empty-state panel">
        <h1>{{ $title ?? '요청 오류' }}</h1>
        <p>{{ $message }}</p>
        <p><a class="button button-primary" href="{{ route('items.index') }}">목록으로</a></p>
    </div>
@endsection
