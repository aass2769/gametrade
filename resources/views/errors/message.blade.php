@extends('layouts.app')

@section('title', $title ?? '요청 오류')

@section('content')
    <p>{{ $message }}</p>
    <p><a href="{{ route('items.index') }}">목록으로</a></p>
@endsection
