@extends('layouts.app')

@section('title', '상품 삭제')

@section('content')
    <h1>상품 삭제</h1>
    <p>아래 상품을 삭제합니다. 삭제한 상품은 되돌릴 수 없습니다.</p>
    <dl>
        <dt>게임</dt>
        <dd>{{ $item->server->game->name }}</dd>
        <dt>서버</dt>
        <dd>{{ $item->server->name }}</dd>
        <dt>아이템명</dt>
        <dd>{{ $item->item_name }}</dd>
        <dt>가격</dt>
        <dd>{{ number_format($item->price) }}원</dd>
    </dl>
    <form method="post" action="{{ route('items.destroy', $item) }}" class="actions">
        @csrf
        @method('DELETE')
        <button type="submit">삭제합니다</button>
        <a class="button secondary" href="{{ route('items.show', $item) }}">취소</a>
    </form>
@endsection
