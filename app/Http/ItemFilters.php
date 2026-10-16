<?php

namespace App\Http;

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
        public readonly ?string $name,
        public readonly ?string $error,
        public readonly string $rawQuery,
        public readonly string $sort,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $raw = trim((string) $request->query('q', ''));
        $gameId = null;
        $gameRaw = $request->query('game_id');

        if ($gameRaw !== null && $gameRaw !== '') {
            $parsed = filter_var($gameRaw, FILTER_VALIDATE_INT);
            if ($parsed !== false && $parsed > 0) {
                $gameId = $parsed;
            }
        }

        $sort = self::normalizeSort($request->query('sort'));

        if (mb_strlen($raw) > 100) {
            return new self($gameId, null, '검색어는 100자 이하로 입력해 주세요.', $raw, $sort);
        }

        $stripped = str_replace(['%', '_'], '', $raw);
        $name = $stripped !== '' ? $stripped : null;

        return new self($gameId, $name, null, $raw, $sort);
    }

    public static function normalizeSort(mixed $sort): string
    {
        $sort = is_string($sort) ? $sort : '';

        return array_key_exists($sort, self::SORTS) ? $sort : 'latest';
    }

    public function isActive(): bool
    {
        return $this->gameId !== null || $this->name !== null;
    }
}
