<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

/**
 * 商品検索のテスト
 */
class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.6: 商品検索機能
     * 「商品名」で部分一致検索ができる
     */
    public function test_can_search_items_by_name()
    {
        Item::factory()->create(['name' => '検索対象の商品']);
        Item::factory()->create(['name' => 'これは関係ない商品']);

        $response = $this->get('/?keyword=検索対象');

        $response->assertStatus(200);
        $response->assertSee('検索対象の商品');
        $response->assertDontSee('これは関係ない商品');
    }

    /**
     * No.6: 商品検索機能
     * 検索状態がマイリストでも保持されている
     */
    public function test_search_keyword_is_kept_on_mylist_tab()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $likedItem = Item::factory()->create(['name' => 'いいねした検索対象']);
        $unlikedItem = Item::factory()->create(['name' => 'いいねしていない検索対象']);
        $user->likedItems()->attach($likedItem->id);

        $response = $this
            ->actingAs($user)
            ->get('/?tab=mylist&keyword=検索対象');

        $response->assertStatus(200);
        $response->assertSee('いいねした検索対象');
        $response->assertDontSee('いいねしていない検索対象');
        $response->assertSee("class='profile-tabs__item profile-tabs__item--active'>マイリスト", false);
        $response->assertSee("value='検索対象'", false);
    }
}