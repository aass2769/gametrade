<?php

namespace Database\Seeders;

use App\Enums\ItemStatus;
use App\Models\Game;
use App\Models\GameServer;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::query()->exists()) {
            $this->seedOriginal();
        }

        $this->seedMoreListings();
    }

    private function seedOriginal(): void
    {
        $seller1 = User::query()->create([
            'username' => 'seller1',
            'password' => 'seller1234',
        ]);
        $seller2 = User::query()->create([
            'username' => 'seller2',
            'password' => 'seller1234',
        ]);

        $lumen = Game::query()->create(['name' => '루멘판타지']);
        $iron = Game::query()->create(['name' => '철기전기']);
        $astra = Game::query()->create(['name' => '아스트라온라인']);

        $lumenServer = GameServer::query()->create(['game_id' => $lumen->id, 'name' => '루멘']);
        GameServer::query()->create(['game_id' => $lumen->id, 'name' => '녹스']);
        $ironServer = GameServer::query()->create(['game_id' => $iron->id, 'name' => '1서버']);
        GameServer::query()->create(['game_id' => $iron->id, 'name' => '2서버']);
        $orion = GameServer::query()->create(['game_id' => $astra->id, 'name' => '오리온']);
        GameServer::query()->create(['game_id' => $astra->id, 'name' => '시리우스']);

        $seller1->items()->create([
            'server_id' => $lumenServer->id,
            'item_name' => '빛나는 포션',
            'price' => 15000,
            'quantity' => 10,
            'description' => '샘플 판매 글입니다.',
            'status' => ItemStatus::OnSale,
        ]);
        $seller1->items()->create([
            'server_id' => $ironServer->id,
            'item_name' => '강철 장검',
            'price' => 42000,
            'quantity' => 1,
            'description' => null,
            'status' => ItemStatus::InProgress,
        ]);
        $seller1->items()->create([
            'server_id' => $orion->id,
            'item_name' => '고대 코어',
            'price' => 900000,
            'quantity' => 2,
            'description' => null,
            'status' => ItemStatus::Completed,
        ]);
        $seller2->items()->create([
            'server_id' => $lumen->servers()->where('name', '녹스')->value('id'),
            'item_name' => '마나 결정',
            'price' => 8000,
            'quantity' => 30,
            'description' => '판매자 seller2의 샘플입니다.',
            'status' => ItemStatus::OnSale,
        ]);
    }

    private function seedMoreListings(): void
    {
        if (User::query()->where('username', 'seller3')->exists()) {
            return;
        }

        $serverIds = GameServer::query()->orderBy('id')->pluck('id');
        if ($serverIds->isEmpty()) {
            return;
        }

        $sellers = [];
        for ($number = 3; $number <= 12; $number++) {
            $sellers[] = User::query()->create([
                'username' => 'seller'.$number,
                'password' => 'seller1234',
            ]);
        }

        $names = [
            '붉은 열매', '푸른 가루', '은빛 단검', '구리 방패', '바람 깃털',
            '이슬 구슬', '검은 잉크', '노란 꽃잎', '단단한 나무', '작은 열쇠',
            '흰 천', '거친 가죽', '맑은 수정', '녹슨 못', '따뜻한 돌',
            '얇은 실', '둥근 방울', '긴 창', '짧은 활', '빈 병',
            '마른 약초', '빛바랜 지도', '작은 종', '굵은 밧줄', '유리 구슬',
            '나무 단추', '철 조각', '부드러운 솜', '오래된 동전', '희미한 등불',
        ];
        $statuses = [ItemStatus::OnSale, ItemStatus::OnSale, ItemStatus::InProgress, ItemStatus::Completed];

        foreach ($names as $index => $name) {
            $seller = $sellers[$index % count($sellers)];
            $item = $seller->items()->create([
                'server_id' => $serverIds[$index % $serverIds->count()],
                'item_name' => $name,
                'price' => 1000 + ($index * 3700),
                'quantity' => ($index % 20) + 1,
                'description' => $index % 3 === 0 ? null : '샘플 판매 글입니다.',
                'status' => $statuses[$index % count($statuses)],
            ]);
            $item->forceFill([
                'created_at' => now()->subHours($index + 1),
                'updated_at' => now()->subHours($index + 1),
            ])->save();
        }
    }
}
