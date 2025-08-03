<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

/**
 * 商品一覧のテスト
 */
class ItemListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.4: 商品一覧取得
     * 全商品を取得できる
     */
    public function test_item_list_displays_all_items()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        Item::factory()->create(['name' => '商品A']);
        Item::factory()->create(['name' => '商品B']);

        $response = $this
            ->actingAs($user)
            ->from('/')
            ->get('/');

        $response->assertStatus(200);
        $response->assertSee('商品A');
        $response->assertSee('商品B');
    }

    /**
     * No.4: 商品一覧取得
     * 購入済み商品は「Sold」と表示される
     */
    public function test_purchased_item_shows_sold_label_on_list()
    {
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->create();

        // 注文を作成することで、商品が購入済みであることを示す
        Order::factory()->create([
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);

        $response = $this
            ->actingAs($buyer) // 購入者本人としてログイン
            ->from('/')
            ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sold');
    }

    /**
     * No.4: 商品一覧取得
     * 自分が出品した商品は表示されない
     */
    public function test_recommended_items_are_not_shown_in_the_list()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();

        Item::factory()->for($seller, 'seller')->create(['name' => '出品した商品']);

        // 出品者でない場合は表示される
        $response = $this
            ->actingAs($buyer) // 購入者としてログイン
            ->get('/');

        $response->assertStatus(200);
        $response->assertSee('出品した商品');

        // 出品者の場合は表示されない
        $response = $this
            ->actingAs($seller) // 出品者としてログイン
            ->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('出品した商品');
    }
}