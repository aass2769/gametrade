@extends('layouts.app')

@section('title', '내 판매 상품')

@section('content')
    <div class="page-heading">
        <div>
            <h1>내 판매 상품</h1>
        </div>
        <a class="button button-primary" href="{{ route('items.create') }}">아이템 등록</a>
    </div>

    @if ($items->isEmpty())
        <div class="empty-state">
            <p>등록한 상품이 없습니다.</p>
        </div>
    @else
        <div class="item-grid">
            @foreach ($items as $item)
                <article class="item-card">
                    <div class="item-card-top">
                        <span class="meta-chip">{{ $item->server->game->name }} · {{ $item->server->name }}</span>
                        <span class="badge badge-{{ $item->status->value }}">{{ $item->status->label() }}</span>
                    </div>
                    <h2 class="item-card-title">
                        <a href="{{ route('items.show', $item) }}">{{ $item->item_name }}</a>
                    </h2>
                    <p class="item-card-price">{{ number_format($item->price) }}원</p>
                    <div class="item-card-meta">
                        <span>수량 {{ $item->quantity }}</span>
                        <span>{{ $item->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="item-card-actions">
                        @if ($item->status === \App\Enums\ItemStatus::OnSale)
                            <a class="button button-primary" href="{{ route('items.edit', $item) }}">수정</a>
                            <a class="button button-secondary" href="{{ route('items.delete', $item) }}">삭제</a>
                        @else
                            <a class="button button-secondary" href="{{ route('items.show', $item) }}">상세</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
