<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.10: 商品購入機能
     * 「購入する」ボタンを押下すると購入が完了する
     */
    public function test_user_can_purchase_an_item()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create();

        $response = $this
            ->actingAs($buyer)
            ->post(route('purchase.store', $item), [
                'payment_method' => 'credit_card',
            ]);

        $response->assertRedirect(route('items.index'));
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);
    }

    /**
     * No.10: 商品購入機能
     * 購入した商品は商品一覧画面にて「sold」と表示される
     */
    public function test_sold_item_is_displayed_as_sold()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create();

        // 購入処理
        $this
            ->actingAs($buyer)
            ->post(route('purchase.store', $item), [
                'payment_method' => 'credit_card',
            ]);

        // 商品一覧ページにアクセス
        $response = $this->actingAs($buyer)->get(route('items.index'));

        $response->assertStatus(200);
        $response->assertSee('Sold');
    }

    /**
     * No.10: 商品購入機能
     * 「プロフィール/購入した商品一覧」に追加されている
     */
    public function test_purchased_item_is_listed_in_profile()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create();

        // 購入処理
        $this
            ->actingAs($buyer)
            ->post(route('purchase.store', $item), [
                'payment_method' => 'credit_card',
            ]);

        // プロフィールページで購入した商品が表示されることを確認
        $response = $this
            ->actingAs($buyer)
            ->get(route('profile.show', ['page' => 'buy']));

        $response->assertStatus(200);
        $response->assertSee($item->name);
    }
}