<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

/**
 * マイリスト一覧のテスト
 */
class MyListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.5: マイリスト一覧取得
     * いいねした商品だけが表示される
     */
    public function test_mylist_shows_only_liked_items()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $likedItem = Item::factory()->create(['name' => 'いいねした商品']);
        $unlikedItem = Item::factory()->create(['name' => 'いいねしていない商品']);

        // いいねを作成
        $user->likedItems()->attach($likedItem->id);

        $response = $this
            ->actingAs($user)
            ->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('いいねした商品');
        $response->assertDontSee('いいねしていない商品');
    }

    /**
     * No.5: マイリスト一覧取得
     * 購入済み商品は「Sold」と表示される
     */
    public function test_purchased_item_shows_sold_label_on_mylist()
    {
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();

        $item = Item::factory()->for($seller, 'seller')->create();

        // 購入処理
        Order::factory()->create([
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);

        // 購入者でログイン
        $response = $this
            ->actingAs($buyer)
            ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sold');
    }

    /**
     * No.5: マイリスト一覧取得
     * 自分が出品した商品は表示されない
     */
    public function test_my_items_are_not_shown_in_mylist()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        $myItem = Item::factory()->for($seller, 'seller')->create(['name' => '自分の商品']);

        $response = $this
            ->actingAs($seller)
            ->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('自分の商品');
    }

    /**
     * No.5: マイリスト一覧取得
     * 未認証の場合は何も表示されない
     */
    public function test_unauthenticated_user_sees_nothing_on_mylist()
    {
        $seller = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create(['name' => '誰かの商品']);

        $response = $this->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertDontSee('誰かの商品');
    }
}