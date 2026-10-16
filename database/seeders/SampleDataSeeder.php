<?php

namespace Database\Seeders;

use App\Enums\ItemStatus;
use App\Models\Game;
use App\Models\GameServer;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class SampleDataSeeder extends Seeder
{
    private const TARGET_LISTING_COUNT = 120;

    private const SELLER_COUNT = 20;

    /**
     * @var array<string, list<string>>
     */
    private const CATALOG = [
        '루멘판타지' => ['루멘', '녹스', '아우라'],
        '철기전기' => ['1서버', '2서버', '3서버'],
        '아스트라온라인' => ['오리온', '시리우스', '베가'],
        '노바크로니클' => ['알파', '베타', '감마'],
        '에메랄드사가' => ['에메랄드', '사파이어', '루비'],
        '크림슨레이드' => ['동부', '서부', '중부'],
        '폴라리스RPG' => ['북극성', '남십자', '카시오페이아'],
        '미스트하버' => ['항구', '등대', '안개섬'],
        '드래곤베일' => ['비늘', '발톱', '숨결'],
    ];

    /**
     * @var list<string>
     */
    private const ITEM_NAMES = [
        '빛나는 포션', '강철 장검', '고대 코어', '마나 결정', '붉은 열매',
        '푸른 가루', '은빛 단검', '구리 방패', '바람 깃털', '이슬 구슬',
        '검은 잉크', '노란 꽃잎', '단단한 나무', '작은 열쇠', '흰 천',
        '거친 가죽', '맑은 수정', '녹슨 못', '따뜻한 돌', '얇은 실',
        '둥근 방울', '긴 창', '짧은 활', '빈 병', '마른 약초',
        '빛바랜 지도', '작은 종', '굵은 밧줄', '유리 구슬', '나무 단추',
        '철 조각', '부드러운 솜', '오래된 동전', '희미한 등불', '푸른 망토',
        '붉은 망토', '강화석 조각', '연성 가루', '고급 회복약', '중급 회복약',
        '하급 회복약', '마력 물약', '신속 물약', '은빛 화살', '금빛 화살',
        '낡은 투구', '견고한 갑옷', '가죽 장갑', '여행자 신발', '마법사 모자',
        '정령의 구슬', '용의 비늘', '불사조 깃털', '심해의 진주', '서리 결정',
        '번개 파편', '어둠의 가루', '빛의 파편', '고대 주문서', '봉인된 상자',
        '미확인 유물', '상급 강화석', '중급 강화석', '하급 강화석', '거래용 상자',
        '희귀 재료 묶음', '연금 키트', '채집용 낫', '낚시용 미끼', '광석 덩어리',
        '보석 원석', '정련된 철괴', '마정석', '룬 조각', '영혼석',
        '축복의 가루', '저주의 가루', '시간의 모래', '공간의 파편', '별빛 가루',
        '달빛 결정', '태양석', '심연의 핵', '성수', '독 바르기',
        '화염 주문서', '빙결 주문서', '치유 주문서', '소환 주문서', '귀환 주문서',
        '길드 인장', '명예의 훈장', '초보자의 검', '숙련자의 활', '전설의 단검',
        '영웅의 방패', '신관의 지팡이', '암살자의 단도', '기사의 창', '음유시인의 하프',
    ];

    public function run(): void
    {
        $this->ensureSellers();
        $this->ensureCatalog();
        $this->ensureListings();
    }

    private function ensureSellers(): void
    {
        for ($number = 1; $number <= self::SELLER_COUNT; $number++) {
            User::query()->firstOrCreate(
                ['username' => 'seller'.$number],
                ['password' => 'seller1234'],
            );
        }
    }

    private function ensureCatalog(): void
    {
        foreach (self::CATALOG as $gameName => $serverNames) {
            $game = Game::query()->firstOrCreate(['name' => $gameName]);

            foreach ($serverNames as $serverName) {
                GameServer::query()->firstOrCreate([
                    'game_id' => $game->id,
                    'name' => $serverName,
                ]);
            }
        }
    }

    private function ensureListings(): void
    {
        $missing = self::TARGET_LISTING_COUNT - Item::query()->count();
        if ($missing <= 0) {
            return;
        }

        /** @var Collection<int, User> $sellers */
        $sellers = User::query()
            ->where('username', 'like', 'seller%')
            ->orderBy('id')
            ->get();

        /** @var Collection<int, int> $serverIds */
        $serverIds = GameServer::query()->orderBy('id')->pluck('id');
        if ($sellers->isEmpty() || $serverIds->isEmpty()) {
            return;
        }

        $statuses = [
            ItemStatus::OnSale,
            ItemStatus::OnSale,
            ItemStatus::OnSale,
            ItemStatus::InProgress,
            ItemStatus::Completed,
        ];
        $existing = Item::query()->count();

        for ($index = 0; $index < $missing; $index++) {
            $absolute = $existing + $index;
            $seller = $sellers[$absolute % $sellers->count()];
            $baseName = self::ITEM_NAMES[$absolute % count(self::ITEM_NAMES)];
            $suffix = intdiv($absolute, count(self::ITEM_NAMES));
            $itemName = $suffix === 0 ? $baseName : $baseName.' #'.($suffix + 1);

            $item = $seller->items()->create([
                'server_id' => $serverIds[$absolute % $serverIds->count()],
                'item_name' => $itemName,
                'price' => 1500 + ($absolute * 2300) % 980000,
                'quantity' => ($absolute % 25) + 1,
                'description' => $absolute % 4 === 0 ? null : '샘플 판매 글입니다.',
                'status' => $statuses[$absolute % count($statuses)],
            ]);

            $item->forceFill([
                'created_at' => now()->subHours($absolute + 1),
                'updated_at' => now()->subHours($absolute + 1),
            ])->save();
        }
    }
}
