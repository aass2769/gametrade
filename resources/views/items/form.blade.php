@extends('layouts.app')

@section('title', $title)

@section('content')
    <h1>{{ $title }}</h1>
    <form method="post" class="stack-form" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif
        <label>
            게임 / 서버
            @error('server_id')<p class="error">{{ $message }}</p>@enderror
            <select name="server_id" required>
                <option value="">선택하세요</option>
                @php $currentGame = null; @endphp
                @foreach ($servers as $server)
                    @if ($currentGame !== $server->game->name)
                        @if ($currentGame !== null)
                            </optgroup>
                        @endif
                        <optgroup label="{{ $server->game->name }}">
                        @php $currentGame = $server->game->name; @endphp
                    @endif
                    <option value="{{ $server->id }}" @selected((string) old('server_id', $item->server_id ?? '') === (string) $server->id)>
                        {{ $server->game->name }} - {{ $server->name }}
                    </option>
                @endforeach
                @if ($currentGame !== null)
                    </optgroup>
                @endif
            </select>
        </label>
        <label>
            아이템명
            @error('item_name')<p class="error">{{ $message }}</p>@enderror
            <input type="text" name="item_name" maxlength="100" required value="{{ old('item_name', $item->item_name ?? '') }}">
        </label>
        <label>
            판매 가격 (원)
            @error('price')<p class="error">{{ $message }}</p>@enderror
            <input type="text" name="price" inputmode="numeric" required value="{{ old('price', $item->price ?? '') }}">
        </label>
        <label>
            판매 수량
            @error('quantity')<p class="error">{{ $message }}</p>@enderror
            <input type="text" name="quantity" inputmode="numeric" required value="{{ old('quantity', $item->quantity ?? '') }}">
        </label>
        <label>
            아이템 설명 (선택)
            @error('description')<p class="error">{{ $message }}</p>@enderror
            <textarea name="description" maxlength="2000" rows="5">{{ old('description', $item->description ?? '') }}</textarea>
        </label>
        <button type="submit">{{ $submitLabel }}</button>
    </form>
@endsection
