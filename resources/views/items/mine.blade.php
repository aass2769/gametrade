@extends('layouts.app')

@section('title', '내 판매 상품')

@section('content')
    <h1>내 판매 상품</h1>
    @if ($items->isEmpty())
        <p>등록한 상품이 없습니다.</p>
    @else
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
                    <th>동작</th>
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
                        <td>{{ $item->status->label() }}</td>
                        <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            @if ($item->status === \App\Enums\ItemStatus::OnSale)
                                <a href="{{ route('items.edit', $item) }}">수정</a>
                                <a href="{{ route('items.delete', $item) }}">삭제</a>
                            @else
                                <a href="{{ route('items.show', $item) }}">상세</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
