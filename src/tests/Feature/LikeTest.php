<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

/** いいね機能 */
class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.8: いいね機能
     * いいねアイコンを押下することによって、いいねした商品として登録することができる。
     */
    public function test_user_can_like_an_item()
    {
        /** @var \App\Models\User $likedUser */
        $likedUser = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this
            ->actingAs($likedUser)
            ->post(route('items.toggle_like', $item));

        $response->assertRedirect();
        $this->assertDatabaseHas('liked_items', [
            'user_id' => $likedUser->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * No.8: いいね機能
     * 追加済みのアイコンは色が変化する
     */
    public function test_liked_icon_color_changes()
    {
        /** @var \App\Models\User $likedUser */
        $likedUser = User::factory()->create();
        $item = Item::factory()->create();

        // いいねしていない状態を確認
        $response = $this
            ->actingAs($likedUser)
            ->get(route('items.show', $item));
        $response->assertStatus(200);
        $response->assertSee('fill=\'#cccccc\'', false);
        $response->assertDontSee('fill=\'#ff2d55\'', false);

        // いいねする
        $likedUser->likedItems()->attach($item->id);

        // いいねした状態を確認
        $response = $this
            ->actingAs($likedUser)
            ->get(route('items.show', $item));
        $response->assertStatus(200);
        $response->assertDontSee('fill=\'#cccccc\'', false);
        $response->assertSee('fill=\'#ff2d55\'', false);
    }

    /**
     * No.8: いいね機能
     * 再度いいねアイコンを押下することによって、いいねを解除することができる。
     */
    public function test_user_can_unlike_an_item()
    {
        /** @var \App\Models\User $likedUser */
        $likedUser = User::factory()->create();
        $item = Item::factory()->create();

        // 先にいいねしておく
        $likedUser->likedItems()->attach($item->id);
        $this->assertDatabaseHas('liked_items', [
            'user_id' => $likedUser->id,
            'item_id' => $item->id,
        ]);

        // いいねを解除
        $response = $this
            ->actingAs($likedUser)
            ->post(route('items.toggle_like', $item));

        $response->assertRedirect();
        $this->assertDatabaseMissing('liked_items', [
            'user_id' => $likedUser->id,
            'item_id' => $item->id,
        ]);
    }
}