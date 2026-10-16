@extends('layouts.app')

@section('title', '전체 게임')

@section('content')
    <div class="page-heading">
        <div>
            <h1>전체 게임</h1>
        </div>
        <a class="button button-ghost" href="{{ route('items.index', ['all' => 1]) }}">전체 상품</a>
    </div>

    @if ($games->isEmpty())
        <div class="empty-state">
            <p>등록된 게임이 없습니다.</p>
        </div>
    @else
        <div class="game-grid">
            @foreach ($games as $game)
                <a class="game-card" href="{{ route('items.index', ['game_id' => $game->id]) }}">
                    @if ($game->imageUrl())
                        <img class="game-card-image" src="{{ $game->imageUrl() }}" alt="" loading="lazy">
                    @endif
                    <span class="game-card-body">
                        <span class="game-card-name">{{ $game->name }}</span>
                        <span class="game-card-meta">{{ number_format($game->items_count) }}건</span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
@endsection
