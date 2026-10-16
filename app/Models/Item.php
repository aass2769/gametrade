<?php

namespace App\Models;

use App\Enums\ItemStatus;
use App\Exceptions\InvalidStatusTransition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    protected $fillable = [
        'user_id',
        'server_id',
        'item_name',
        'price',
        'quantity',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ItemStatus::class,
            'price' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }

    public function scopeFiltered(Builder $query, ?int $gameId, ?string $name, ?int $serverId = null): Builder
    {
        return $query
            ->when($serverId !== null, function (Builder $query) use ($serverId): void {
                $query->where('server_id', $serverId);
            })
            ->when($serverId === null && $gameId !== null, function (Builder $query) use ($gameId): void {
                $query->whereHas('server', function (Builder $server) use ($gameId): void {
                    $server->where('game_id', $gameId);
                });
            })
            ->when($name !== null, function (Builder $query) use ($name): void {
                $query->where('item_name', 'like', '%'.$name.'%');
            });
    }

    public function scopeOrdered(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    public function revise(array $data): bool
    {
        $updated = $this->newQuery()
            ->whereKey($this->id)
            ->where('user_id', $this->user_id)
            ->where('status', ItemStatus::OnSale->value)
            ->update([
                'server_id' => $data['server_id'],
                'item_name' => $data['item_name'],
                'price' => $data['price'],
                'quantity' => $data['quantity'],
                'description' => $data['description'],
            ]);

        if ($updated === 1) {
            $this->refresh();

            return true;
        }

        $fresh = $this->fresh();

        return $fresh !== null
            && $fresh->user_id === $this->user_id
            && $fresh->status === ItemStatus::OnSale;
    }

    public function removeListing(): bool
    {
        $deleted = $this->newQuery()
            ->whereKey($this->id)
            ->where('user_id', $this->user_id)
            ->where('status', ItemStatus::OnSale->value)
            ->delete();

        return $deleted === 1;
    }

    public function transitionTo(ItemStatus $next): void
    {
        DB::transaction(function () use ($next): void {
            $locked = static::query()->whereKey($this->id)->lockForUpdate()->first();

            if ($locked === null || ! $locked->status->canTransitionTo($next)) {
                throw new InvalidStatusTransition('허용되지 않는 상태 변경입니다.');
            }

            $locked->status = $next;
            $locked->save();
        });

        $this->refresh();
    }
}
