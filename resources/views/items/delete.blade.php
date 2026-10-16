@extends('layouts.app')

@section('title', '상품 삭제')

@section('content')
    <div class="form-shell">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Danger zone</p>
                <h1>상품 삭제</h1>
                <p class="lede">아래 상품을 삭제합니다. 삭제한 상품은 되돌릴 수 없습니다.</p>
            </div>
        </div>

        <section class="panel">
            <dl class="detail-list">
                <dt>게임</dt>
                <dd>{{ $item->server->game->name }}</dd>
                <dt>서버</dt>
                <dd>{{ $item->server->name }}</dd>
                <dt>아이템명</dt>
                <dd>{{ $item->item_name }}</dd>
                <dt>가격</dt>
                <dd>{{ number_format($item->price) }}원</dd>
            </dl>
            <form method="post" action="{{ route('items.destroy', $item) }}" class="actions delete-actions">
                @csrf
                @method('DELETE')
                <button type="submit" class="button button-danger">삭제</button>
                <a class="button button-secondary" href="{{ route('items.show', $item) }}">취소</a>
            </form>
        </section>
    </div>
@endsection
