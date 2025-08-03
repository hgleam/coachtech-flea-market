<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Enums\ItemCondition;

/**
 * 商品出品のテスト
 */
class ItemExhibitionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.15: 出品商品情報登録
     * 商品出品画面にて必要な情報が保存できること（カテゴリ、商品の状態、商品名、商品の説明、販売価格）
     */
    public function test_user_can_create_an_item()
    {
        Storage::fake('public');
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('item.jpg');
        $category = Category::factory()->create(['name' => 'テストカテゴリ']);
        $condition = ItemCondition::MINT->value;


        $response = $this
            ->actingAs($user)
            ->from('/sell')
            ->post('/sell', [
                'image' => $file,
                'categories' => [$category->id],
                'condition' => $condition,
                'brand_name' => 'テストブランド',
                'name' => 'テスト商品',
                'description' => 'これはテスト用の商品説明です。',
                'price' => 1000,
            ]);

        $item = Item::first();
        $response->assertRedirect('/');
        $this->assertDatabaseHas('items', [
            'name' => 'テスト商品',
            'seller_id' => $user->id,
            'price' => 1000,
            'brand_name' => 'テストブランド',
            'condition' => $condition,
        ]);
        $this->assertDatabaseHas('item_categories', [
            'item_id' => $item->id,
            'category_id' => $category->id,
        ]);
        $this->assertTrue(Storage::disk('public')->exists('items/' . $file->hashName()));
    }
}