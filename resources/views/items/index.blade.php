@extends('layouts.app')

@section('title', '아이템 목록')

@section('content')
    <h1>아이템 목록</h1>
    <form class="filters" method="get" action="{{ route('items.index') }}">
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
            아이템명
            <input type="text" name="q" maxlength="100" value="{{ $filters->rawQuery }}">
        </label>
        <label>
            정렬
            <select name="sort">
                @foreach (\App\Http\ItemFilters::SORTS as $value => $label)
                    <option value="{{ $value }}" @selected($filters->sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit">검색</button>
    </form>
    @if ($filters->error)
        <p class="error">{{ $filters->error }}</p>
    @elseif ($items->isEmpty() && $items->total() === 0)
        <p>{{ $filters->isActive() ? '검색 결과가 없습니다.' : '등록된 아이템이 없습니다.' }}</p>
    @elseif ($items->isNotEmpty())
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>게임</th>
                    <th>서버</th>
                    <th>아이템명</th>
                    <th>가격</th>
                    <th>수량</th>
                    <th>상태</th>
                    <th>등록일</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->server->game->name }}</td>
                        <td>{{ $item->server->name }}</td>
                        <td><a href="{{ route('items.show', $item) }}">{{ $item->item_name }}</a></td>
                        <td>{{ number_format($item->price) }}원</td>
                        <td>{{ $item->quantity }}</td>
                        <td><span class="badge badge-{{ $item->status->value }}">{{ $item->status->label() }}</span></td>
                        <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
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
