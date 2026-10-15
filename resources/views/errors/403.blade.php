@extends('layouts.app')

@section('content')
    <p>{{ $exception->getMessage() }}</p>
    <p><a href="{{ route('items.index') }}">목록으로</a></p>
@endsection
