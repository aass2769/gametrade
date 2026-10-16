@extends('layouts.app')

@section('title', $selectedGame?->name ?? '전체 상품')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $selectedGame?->name ?? '전체 상품' }}</h1>
        </div>
        <a class="button button-ghost" href="{{ route('items.index') }}">전체 게임</a>
    </div>

    <form class="filters filter-panel" method="get" action="{{ route('items.index') }}">
        @unless ($filters->gameId)
            <input type="hidden" name="all" value="1">
        @endunless
        <label>
            게임
            <select name="game_id">
                <option value="">전체</option>
                @foreach ($games as $game)
                    <option value="{{ $game->id }}" @selected($filters->gameId === $game->id)>{{ $game->name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            서버
            <select name="server_id" @disabled($servers->isEmpty())>
                <option value="">전체</option>
                @foreach ($servers as $server)
                    <option value="{{ $server->id }}" @selected($filters->serverId === $server->id)>{{ $server->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="filter-grow">
            아이템명
            <input type="text" name="q" maxlength="100" value="{{ $filters->rawQuery }}" placeholder="아이템명 검색">
        </label>
        <label>
            정렬
            <select name="sort">
                @foreach (\App\Http\ItemFilters::SORTS as $value => $label)
                    <option value="{{ $value }}" @selected($filters->sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="button button-primary">검색</button>
    </form>

    @if ($filters->error)
        <p class="error">{{ $filters->error }}</p>
    @elseif ($items->isEmpty() && $items->total() === 0)
        <div class="empty-state">
            <p>{{ $filters->isActive() ? '검색 결과가 없습니다.' : '등록된 아이템이 없습니다.' }}</p>
        </div>
    @elseif ($items->isNotEmpty())
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
                </article>
            @endforeach
        </div>
    @endif

    @if ($items && ($items->lastPage() > 1 || $items->currentPage() > 1))
        <nav class="pager" aria-label="페이지">
            @if ($items->currentPage() > 1)
                <a href="{{ $items->url(min($items->currentPage() - 1, $items->lastPage())) }}">이전</a>
            @endif
            @for ($page = 1; $page <= $items->lastPage(); $page++)
                @if ($page === $items->currentPage())
                    <span aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $items->url($page) }}">{{ $page }}</a>
                @endif
            @endfor
            @if ($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}">다음</a>
            @endif
        </nav>
    @endif
@endsection
