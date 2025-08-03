<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.13: ユーザー情報取得
     * 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
     */
    public function test_user_can_view_their_profile()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $itemSold = Item::factory()->create(['seller_id' => $user->id]);

        // 購入済み商品を作成
        $itemPurchased = Item::factory()->create();
        Order::factory()->create([
            'item_id' => $itemPurchased->id,
            'buyer_id' => $user->id,
        ]);

        // 1. 出品した商品が表示されることを確認
        $response_sell = $this
            ->actingAs($user)
            ->from('/')
            ->get('/mypage');

        $response_sell->assertStatus(200);
        $response_sell->assertSee($user->name);
        $response_sell->assertSee($itemSold->name);
        $response_sell->assertDontSee($itemPurchased->name);

        // 2. 購入した商品が表示されることを確認
        $response_buy = $this
            ->actingAs($user)
            ->from('/mypage')
            ->get('/mypage?page=buy');

        $response_buy->assertStatus(200);
        $response_buy->assertSee($user->name);
        $response_buy->assertSee($itemPurchased->name);
        $response_buy->assertDontSee($itemSold->name);
    }

    /**
     * No.14: ユーザー情報変更
     * 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
     */
    public function test_profile_edit_page_shows_initial_values()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'zip_code' => '123-4567',
            'address' => '東京都千代田区',
            'building' => 'テストビル101',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/mypage')
            ->get('/mypage/profile');

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('123-4567');
        $response->assertSee('東京都千代田区');
        $response->assertSee('テストビル101');
    }
}