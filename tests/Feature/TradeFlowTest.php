<?php

namespace Tests\Feature;

use App\Enums\ItemStatus;
use App\Models\Game;
use App\Models\GameServer;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TradeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_and_cannot_see_edit_controls(): void
    {
        [$game, $server, $seller] = $this->catalog();
        $item = $this->listing($seller, $server, '빛나는 포션', 15000);

        $this->get('/')
            ->assertOk()
            ->assertSee('빛나는 포션')
            ->assertSee('15,000원')
            ->assertSee($game->name)
            ->assertDontSee('수정');

        $this->get('/items/'.$item->id)
            ->assertOk()
            ->assertSee('판매자')
            ->assertSee('seller1')
            ->assertDontSee('$2y$')
            ->assertDontSee('상태 변경');
    }

    public function test_missing_item_is_not_found(): void
    {
        $this->get('/items/999')->assertNotFound()->assertSee('상품을 찾을 수 없습니다.');
        $this->get('/missing')->assertNotFound()->assertSee('페이지를 찾을 수 없습니다.');
    }

    public function test_search_combines_game_and_name_and_ignores_sql_fragments(): void
    {
        [, $server, $seller] = $this->catalog();
        $otherGame = Game::query()->create(['name' => '철기전기']);
        $otherServer = GameServer::query()->create(['game_id' => $otherGame->id, 'name' => '1서버']);
        $this->listing($seller, $server, '빛나는 포션', 15000);
        $this->listing($seller, $otherServer, '강철 장검', 42000);

        $this->get('/?'.http_build_query([
            'game_id' => $otherGame->id,
            'q' => '장검',
        ]))
            ->assertOk()
            ->assertSee('강철 장검')
            ->assertDontSee('빛나는 포션');

        $this->get('/?q='.urlencode("' OR 1=1 --"))
            ->assertOk()
            ->assertSee('검색 결과가 없습니다.')
            ->assertDontSee('빛나는 포션');
    }

    public function test_list_sort_accepts_only_the_allowed_orders(): void
    {
        [, $server, $seller] = $this->catalog();
        $expensive = $this->listing($seller, $server, '비싼 검', 30000);
        $cheap = $this->listing($seller, $server, '싼 포션', 10000);
        $middle = $this->listing($seller, $server, '중간 방패', 20000);
        $expensive->forceFill(['created_at' => '2026-01-01 00:00:00'])->save();
        $cheap->forceFill(['created_at' => '2026-01-02 00:00:00'])->save();
        $middle->forceFill(['created_at' => '2026-01-03 00:00:00'])->save();

        $this->get('/?sort=latest')
            ->assertOk()
            ->assertSee('최신순')
            ->assertSee('가격 낮은 순')
            ->assertSee('가격 높은 순')
            ->assertSeeInOrder(['중간 방패', '싼 포션', '비싼 검']);
        $this->get('/?sort=price_asc')->assertOk()->assertSeeInOrder(['싼 포션', '중간 방패', '비싼 검']);
        $this->get('/?sort=price_desc')->assertOk()->assertSeeInOrder(['비싼 검', '중간 방패', '싼 포션']);
        $this->get('/?'.http_build_query(['sort' => 'name;drop']))
            ->assertOk()
            ->assertSeeInOrder(['중간 방패', '싼 포션', '비싼 검']);
        $this->get('/')->assertOk()->assertSeeInOrder(['중간 방패', '싼 포션', '비싼 검']);

        $this->getJson('/api/v1/items?sort=price_asc')
            ->assertOk()
            ->assertJsonPath('data.0.item_name', '싼 포션')
            ->assertJsonPath('data.1.item_name', '중간 방패')
            ->assertJsonPath('data.2.item_name', '비싼 검');
    }

    public function test_register_logs_in_and_rejects_duplicate_username(): void
    {
        $this->post('/register', [
            'username' => 'new_user',
            'password' => 'password1',
            'password_confirm' => 'password1',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $user = User::query()->where('username', 'new_user')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password1', $user->password));

        $this->post('/logout');

        $this->post('/register', [
            'username' => 'new_user',
            'password' => 'password1',
            'password_confirm' => 'password1',
        ])->assertSessionHasErrors('username');
    }

    public function test_login_failure_message_does_not_reveal_which_field_was_wrong(): void
    {
        User::factory()->create(['username' => 'seller1']);

        $this->from('/login')->post('/login', [
            'username' => 'seller1',
            'password' => 'wrong-pass',
        ])->assertSessionHasErrors([
            'login' => '아이디 또는 비밀번호가 올바르지 않습니다.',
        ]);

        $this->from('/login')->post('/login', [
            'username' => 'nobody',
            'password' => 'wrong-pass',
        ])->assertSessionHasErrors([
            'login' => '아이디 또는 비밀번호가 올바르지 않습니다.',
        ]);
    }

    public function test_logout_is_post_only(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/logout')->assertStatus(405);
        $this->assertAuthenticated();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_cannot_create_and_validation_rejects_bad_numbers(): void
    {
        [, $server] = $this->catalog();

        $this->get('/items/create')->assertRedirect('/login');
        $this->assertDatabaseCount('items', 0);

        $seller = User::factory()->create();
        $payload = [
            'server_id' => $server->id,
            'item_name' => '포션',
            'price' => '1.5',
            'quantity' => '1',
            'description' => '',
        ];

        $this->actingAs($seller)->post('/items', $payload)->assertSessionHasErrors('price');
        $this->actingAs($seller)->post('/items', [...$payload, 'price' => '0'])->assertSessionHasErrors('price');
        $this->actingAs($seller)->post('/items', [...$payload, 'price' => '-1', 'quantity' => '0'])->assertSessionHasErrors(['price', 'quantity']);
        $this->actingAs($seller)->post('/items', [...$payload, 'item_name' => '', 'server_id' => ''])->assertSessionHasErrors(['item_name', 'server_id']);
        $this->assertDatabaseCount('items', 0);
    }

    public function test_owner_can_edit_and_other_user_cannot_change_the_row(): void
    {
        [, $server, $owner] = $this->catalog();
        $item = $this->listing($owner, $server, '빛나는 포션', 15000);
        $other = User::factory()->create(['username' => 'seller2']);

        $this->actingAs($other)->get('/items/'.$item->id.'/edit')
            ->assertForbidden()
            ->assertSee('이 상품을 수정할 수 없습니다.');

        $this->actingAs($owner)->put('/items/'.$item->id, [
            'server_id' => $server->id,
            'item_name' => '수정된 포션',
            'price' => '16000',
            'quantity' => '2',
            'description' => '',
        ])->assertRedirect('/items/'.$item->id);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'item_name' => '수정된 포션',
            'price' => 16000,
            'description' => null,
        ]);

        $this->actingAs($other)->delete('/items/'.$item->id)->assertForbidden();
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    public function test_status_changes_follow_the_allowed_path(): void
    {
        [, $server, $owner] = $this->catalog();
        $item = $this->listing($owner, $server, '강철 장검', 42000);

        $this->actingAs($owner)->post('/items/'.$item->id.'/status', ['status' => 'COMPLETED'])
            ->assertStatus(400);
        $this->assertSame(ItemStatus::OnSale, $item->refresh()->status);

        $this->actingAs($owner)->post('/items/'.$item->id.'/status', ['status' => 'NOT_A_STATUS'])
            ->assertStatus(400);
        $this->assertSame(ItemStatus::OnSale, $item->refresh()->status);

        $this->actingAs($owner)->post('/items/'.$item->id.'/status', ['status' => 'IN_PROGRESS'])
            ->assertRedirect('/items/'.$item->id);
        $this->assertSame(ItemStatus::InProgress, $item->refresh()->status);

        $this->actingAs($owner)->put('/items/'.$item->id, [
            'server_id' => $server->id,
            'item_name' => '바꾸면 안 됨',
            'price' => '1',
            'quantity' => '1',
        ])->assertForbidden();
        $this->assertSame('강철 장검', $item->refresh()->item_name);

        $this->actingAs($owner)->post('/items/'.$item->id.'/status', ['status' => 'ON_SALE'])
            ->assertRedirect('/items/'.$item->id);
        $this->actingAs($owner)->post('/items/'.$item->id.'/status', ['status' => 'IN_PROGRESS']);
        $this->actingAs($owner)->post('/items/'.$item->id.'/status', ['status' => 'COMPLETED'])
            ->assertRedirect('/items/'.$item->id);
        $this->assertSame(ItemStatus::Completed, $item->refresh()->status);

        $this->actingAs($owner)->post('/items/'.$item->id.'/status', ['status' => 'ON_SALE'])
            ->assertStatus(400);
    }

    public function test_my_items_ignores_user_id_query_and_escapes_names(): void
    {
        [, $server, $owner] = $this->catalog();
        $other = User::factory()->create(['username' => 'seller2']);
        $this->listing($owner, $server, '<script>alert(1)</script>', 15000, ItemStatus::OnSale);
        $this->listing($other, $server, '마나 결정', 8000, ItemStatus::OnSale);
        $progress = $this->listing($owner, $server, '강철 장검', 42000, ItemStatus::InProgress);

        $response = $this->actingAs($owner)->get('/my/items?user_id='.$other->id);
        $response->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('강철 장검')
            ->assertDontSee('마나 결정');

        $html = $response->getContent();
        $this->assertStringContainsString('수정', $html);
        $this->assertStringContainsString('/items/'.$progress->id.'"', $html);
    }

    public function test_api_token_can_manage_only_owned_items(): void
    {
        [, $server, $owner] = $this->catalog();
        $item = $this->listing($owner, $server, '빛나는 포션', 15000);

        $this->getJson('/api/v1/my/items')->assertUnauthorized();

        $registered = $this->postJson('/api/v1/register', [
            'username' => 'api_user',
            'password' => 'password1',
            'password_confirm' => 'password1',
        ])->assertCreated();
        $token = $registered->json('token');
        $this->assertIsString($token);

        $created = $this->withToken($token)->postJson('/api/v1/items', [
            'server_id' => $server->id,
            'item_name' => 'API 포션',
            'price' => 1000,
            'quantity' => 1,
        ])->assertCreated();
        $created->assertJsonPath('data.item_name', 'API 포션');
        $created->assertJsonMissingPath('data.password');
        $this->assertStringNotContainsString('$2y$', $created->getContent());

        $this->withToken($token)->putJson('/api/v1/items/'.$item->id, [
            'server_id' => $server->id,
            'item_name' => '가로채기',
            'price' => 1,
            'quantity' => 1,
        ])->assertForbidden();
        $this->assertSame('빛나는 포션', $item->refresh()->item_name);

        $this->withToken($token)->getJson('/api/v1/games')->assertOk()->assertJsonFragment(['name' => '루멘판타지']);
        $this->withToken($token)->getJson('/api/v1/my/items')->assertOk()->assertJsonFragment(['item_name' => 'API 포션']);
    }

    /**
     * @return array{0: Game, 1: GameServer, 2: User}
     */
    private function catalog(): array
    {
        $game = Game::query()->create(['name' => '루멘판타지']);
        $server = GameServer::query()->create(['game_id' => $game->id, 'name' => '루멘']);
        $seller = User::factory()->create(['username' => 'seller1']);

        return [$game, $server, $seller];
    }

    private function listing(User $seller, GameServer $server, string $name, int $price, ItemStatus $status = ItemStatus::OnSale): Item
    {
        return $seller->items()->create([
            'server_id' => $server->id,
            'item_name' => $name,
            'price' => $price,
            'quantity' => 1,
            'description' => null,
            'status' => $status,
        ]);
    }
}
