<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.12: 配送先変更機能
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     * 購入した商品に送付先住所が紐づいて登録される
     */
    public function test_address_update_and_purchase_flow()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create();

        $addressData = [
            'zip_code' => '123-4567',
            'address' => '東京都テスト区テスト町1-2-3',
            'building' => 'テストビル101',
        ];

        // 住所を更新
        $this
            ->actingAs($buyer)
            ->put(route('address.update', $item), $addressData);

        // 購入ページにリダイレクトされ、更新した住所が表示されていることを確認
        $response = $this
            ->actingAs($buyer)
            ->get(route('purchase.create', $item));

        $response->assertStatus(200);
        $response->assertSee($addressData['zip_code']);
        $response->assertSee($addressData['address']);
        $response->assertSee($addressData['building']);
    }

    /**
     * No.12: 配送先変更機能
     * 購入した商品に送付先住所が紐づいて登録される
     */
    public function test_purchased_item_has_shipping_address()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create();

        $addressData = [
            'zip_code' => '123-4567',
            'address' => '東京都テスト区テスト町1-2-3',
            'building' => 'テストビル101',
        ];

        $this
            ->actingAs($buyer)
            ->put(route('address.update', $item), $addressData);

        $this
            ->actingAs($buyer)
            ->post(route('purchase.store', $item), [
                'payment_method' => 'credit_card',
            ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'zip_code' => $addressData['zip_code'],
            'address' => $addressData['address'],
            'building' => $addressData['building'],
        ]);
    }
}