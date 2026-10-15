@extends('layouts.app')

@section('content')
    <p>{{ $exception->getMessage() !== '' ? $exception->getMessage() : '페이지를 찾을 수 없습니다.' }}</p>
    <p><a href="{{ route('items.index') }}">목록으로</a></p>
@endsection
