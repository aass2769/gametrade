<?php

namespace App\Http;

use App\Models\GameServer;
use Illuminate\Http\Request;

class ItemFilters
{
    public const PER_PAGE = 10;

    public const SORTS = [
        'latest' => '최신순',
        'price_asc' => '가격 낮은 순',
        'price_desc' => '가격 높은 순',
    ];

    public function __construct(
        public readonly ?int $gameId,
        public readonly ?int $serverId,
        public readonly ?string $name,
        public readonly ?string $error,
        public readonly string $rawQuery,
        public readonly string $sort,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $raw = trim((string) $request->query('q', ''));
        $gameId = self::positiveInt($request->query('game_id'));
        $serverId = self::positiveInt($request->query('server_id'));
        $sort = self::normalizeSort($request->query('sort'));

        if ($serverId !== null) {
            $server = GameServer::query()->find($serverId);

            if ($server === null) {
                $serverId = null;
            } elseif ($gameId === null) {
                $gameId = $server->game_id;
            } elseif ($server->game_id !== $gameId) {
                $serverId = null;
            }
        }

        if (mb_strlen($raw) > 100) {
            return new self($gameId, $serverId, null, '검색어는 100자 이하로 입력해 주세요.', $raw, $sort);
        }

        $stripped = str_replace(['%', '_'], '', $raw);
        $name = $stripped !== '' ? $stripped : null;

        return new self($gameId, $serverId, $name, null, $raw, $sort);
    }

    public static function normalizeSort(mixed $sort): string
    {
        $sort = is_string($sort) ? $sort : '';

        return array_key_exists($sort, self::SORTS) ? $sort : 'latest';
    }

    public function isActive(): bool
    {
        return $this->gameId !== null || $this->serverId !== null || $this->name !== null;
    }

    private static function positiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT);

        return $parsed !== false && $parsed > 0 ? $parsed : null;
    }
}
