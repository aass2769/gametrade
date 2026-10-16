@extends('layouts.app')

@section('title', $item->item_name)

@section('content')
    @php
        $isOwner = auth()->check() && auth()->id() === $item->user_id;
        $canEdit = $isOwner && $item->status === \App\Enums\ItemStatus::OnSale;
        $nextStatuses = $isOwner ? $item->status->allowedNext() : [];
    @endphp

    <div class="detail-hero">
        <div class="detail-hero-main">
            <p class="eyebrow">{{ $item->server->game->name }} · {{ $item->server->name }}</p>
            <h1>{{ $item->item_name }}</h1>
            <p class="detail-price">{{ number_format($item->price) }}원</p>
            <div class="detail-tags">
                <span class="badge badge-{{ $item->status->value }}">{{ $item->status->label() }}</span>
                <span class="meta-chip">수량 {{ $item->quantity }}</span>
            </div>
        </div>
        @if ($canEdit)
            <p class="actions detail-actions">
                <a class="button button-primary" href="{{ route('items.edit', $item) }}">수정</a>
                <a class="button button-secondary" href="{{ route('items.delete', $item) }}">삭제</a>
            </p>
        @endif
    </div>

    <div class="detail-grid">
        <section class="panel">
            <h2 class="panel-title">상품 정보</h2>
            <dl class="detail-list">
                <dt>게임</dt>
                <dd>{{ $item->server->game->name }}</dd>
                <dt>서버</dt>
                <dd>{{ $item->server->name }}</dd>
                <dt>가격</dt>
                <dd>{{ number_format($item->price) }}원</dd>
                <dt>수량</dt>
                <dd>{{ $item->quantity }}</dd>
                <dt>상태</dt>
                <dd>{{ $item->status->label() }}</dd>
                <dt>판매자</dt>
                <dd>{{ $item->seller->username }}</dd>
                <dt>설명</dt>
                <dd>
                    @if ($item->description === null || $item->description === '')
                        설명이 없습니다.
                    @else
                        {!! nl2br(e($item->description)) !!}
                    @endif
                </dd>
                <dt>등록일</dt>
                <dd>{{ $item->created_at?->format('Y-m-d H:i') }}</dd>
                <dt>수정일</dt>
                <dd>{{ $item->updated_at?->format('Y-m-d H:i') }}</dd>
            </dl>
        </section>

        @if ($nextStatuses !== [])
            <section class="panel">
                <h2 class="panel-title">상태 관리</h2>
                <form method="post" action="{{ route('items.status', $item) }}" class="stack-form">
                    @csrf
                    <label>
                        거래 상태 변경
                        <select name="status">
                            @foreach ($nextStatuses as $next)
                                <option value="{{ $next->value }}">{{ $next->label() }}</option>
                            @endforeach
                        </select>
                    </label>
                    @if ($item->status === \App\Enums\ItemStatus::InProgress)
                        <p class="help-text">판매 중으로 바꾸면 진행 표시를 취소합니다.</p>
                    @endif
                    <button type="submit" class="button button-primary">상태 변경</button>
                </form>
            </section>
        @endif
    </div>
@endsection
