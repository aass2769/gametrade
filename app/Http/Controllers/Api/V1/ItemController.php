<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ItemStatus;
use App\Http\Controllers\Controller;
use App\Http\ItemFilters;
use App\Http\Requests\ItemRequest;
use App\Http\Requests\UpdateItemStatusRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $filters = ItemFilters::fromRequest($request);
        if ($filters->error !== null) {
            return response()->json(['message' => $filters->error], 422);
        }

        $items = Item::query()
            ->with(['server.game', 'seller'])
            ->filtered($filters->gameId, $filters->name)
            ->ordered($filters->sort)
            ->paginate(ItemFilters::PER_PAGE)
            ->withQueryString();

        return ItemResource::collection($items);
    }

    public function show(Item $item): ItemResource
    {
        $item->load(['server.game', 'seller']);

        return new ItemResource($item);
    }

    public function store(ItemRequest $request): JsonResponse
    {
        $item = $request->user()->items()->create([
            ...$request->validated(),
            'status' => ItemStatus::OnSale,
        ]);
        $item->load(['server.game', 'seller']);

        return (new ItemResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function update(ItemRequest $request, Item $item): ItemResource
    {
        $this->authorize('update', $item);

        if (! $item->revise($request->validated())) {
            abort(403, '현재 상태에서는 수정할 수 없습니다.');
        }

        $item->load(['server.game', 'seller']);

        return new ItemResource($item);
    }

    public function destroy(Item $item): JsonResponse
    {
        $this->authorize('delete', $item);

        if (! $item->removeListing()) {
            abort(403, '현재 상태에서는 삭제할 수 없습니다.');
        }

        return response()->json(['message' => '상품을 삭제했습니다.']);
    }

    public function updateStatus(UpdateItemStatusRequest $request, Item $item): ItemResource
    {
        $this->authorize('changeStatus', $item);
        $item->transitionTo(ItemStatus::from($request->validated('status')));
        $item->load(['server.game', 'seller']);

        return new ItemResource($item);
    }

    public function mine(Request $request): AnonymousResourceCollection
    {
        $items = $request->user()->items()
            ->with(['server.game', 'seller'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return ItemResource::collection($items);
    }
}
