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
        if (User::query()->exists()) {
            return;
        }

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
}
