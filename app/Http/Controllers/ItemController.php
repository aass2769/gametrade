<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Http\ItemFilters;
use App\Http\Requests\ItemRequest;
use App\Http\Requests\UpdateItemStatusRequest;
use App\Models\Game;
use App\Models\GameServer;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ItemFilters::fromRequest($request);
        $items = $filters->error === null
            ? $this->listings($filters)
            : new Collection;

        return view('items.index', [
            'games' => Game::query()->orderBy('id')->get(),
            'items' => $items,
            'filters' => $filters,
        ]);
    }

    public function show(Item $item): View
    {
        $item->load(['server.game', 'seller']);

        return view('items.show', ['item' => $item]);
    }

    public function create(): View
    {
        return view('items.form', [
            'title' => '아이템 등록',
            'action' => route('items.store'),
            'method' => 'POST',
            'submitLabel' => '등록',
            'item' => null,
            'servers' => $this->servers(),
        ]);
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        $item = $request->user()->items()->create([
            ...$request->validated(),
            'status' => ItemStatus::OnSale,
        ]);

        return redirect()
            ->route('items.show', $item)
            ->with('status', '상품을 등록했습니다.');
    }

    public function edit(Item $item): View
    {
        $this->authorize('update', $item);
        $item->load('server.game');

        return view('items.form', [
            'title' => '아이템 수정',
            'action' => route('items.update', $item),
            'method' => 'PUT',
            'submitLabel' => '저장',
            'item' => $item,
            'servers' => $this->servers(),
        ]);
    }

    public function update(ItemRequest $request, Item $item): RedirectResponse
    {
        $this->authorize('update', $item);

        if (! $item->revise($request->validated())) {
            abort(403, '현재 상태에서는 수정할 수 없습니다.');
        }

        return redirect()
            ->route('items.show', $item)
            ->with('status', '상품을 수정했습니다.');
    }

    public function confirmDelete(Item $item): View
    {
        $this->authorize('delete', $item);
        $item->load('server.game');

        return view('items.delete', ['item' => $item]);
    }

    public function destroy(Item $item): RedirectResponse
    {
        $this->authorize('delete', $item);

        if (! $item->removeListing()) {
            abort(403, '현재 상태에서는 삭제할 수 없습니다.');
        }

        return redirect()
            ->route('items.index')
            ->with('status', '상품을 삭제했습니다.');
    }

    public function updateStatus(UpdateItemStatusRequest $request, Item $item): RedirectResponse
    {
        $this->authorize('changeStatus', $item);
        $item->transitionTo(ItemStatus::from($request->validated('status')));

        return redirect()
            ->route('items.show', $item)
            ->with('status', '거래 상태를 변경했습니다.');
    }

    public function mine(Request $request): View
    {
        $items = $request->user()->items()
            ->with(['server.game'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('items.mine', ['items' => $items]);
    }

    private function listings(ItemFilters $filters): Collection
    {
        return Item::query()
            ->with(['server.game', 'seller'])
            ->filtered($filters->gameId, $filters->name)
            ->ordered($filters->sort)
            ->get();
    }

    private function servers(): Collection
    {
        return GameServer::query()
            ->with('game')
            ->orderBy('game_id')
            ->orderBy('id')
            ->get();
    }
}
