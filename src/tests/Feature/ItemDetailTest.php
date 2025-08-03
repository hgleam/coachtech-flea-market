<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Enums\ItemCondition;

/**
 * 商品詳細のテスト
 */
class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.7: 商品詳細情報取得
     * 必要な情報が表示される（商品画像、商品名、ブランド名、価格、いいね数、コメント数、商品説明、商品情報（カテゴリ、商品の状態）、コメント数、コメントしたユーザー情報、コメント内容）
     */
    public function test_item_detail_page_shows_all_information()
    {
        $category1 = Category::factory()->create(['name' => 'テストカテゴリ1']);
        $category2 = Category::factory()->create(['name' => 'テストカテゴリ2']);
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'brand_name' => 'テストブランド',
            'condition' => ItemCondition::MINT->value,
        ]);
        $item->categories()->attach([$category1->id, $category2->id]);


        $response = $this
            ->actingAs($seller)
            ->from('/')
            ->get('/item/' . $item->id);

        $response->assertStatus(200);
        $response->assertSee($item->name);
        $response->assertSee($item->brand_name);
        $response->assertSee(number_format($item->price));
        $response->assertSee($item->description);
        $response->assertSee(ItemCondition::MINT->value);
        $response->assertSee($category1->name);
        $response->assertSee($category2->name);
    }

    /**
     * No.8: 商品詳細情報取得
     * 複数選択されたカテゴリが表示されているか
     */
    public function test_multiple_selected_categories_are_displayed()
    {
        $category1 = Category::factory()->create(['name' => 'テストカテゴリ1']);
        $category2 = Category::factory()->create(['name' => 'テストカテゴリ2']);
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create();
        $item->categories()->attach([$category1->id, $category2->id]);

        $response = $this
            ->actingAs($seller)
            ->get('/item/' . $item->id);
        $response->assertStatus(200);
        $response->assertSee($category1->name);
        $response->assertSee($category2->name);
    }
}