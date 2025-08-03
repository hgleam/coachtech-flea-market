<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

/** コメント送信機能 */
class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.9: コメント送信機能
     * ログイン済みのユーザーはコメントを送信できる
     */
    public function test_authenticated_user_can_post_a_comment()
    {
        /** @var \App\Models\User $commentedUser */
        $commentedUser = User::factory()->create();
        $item = Item::factory()->create();
        $comment = 'これはテストコメントです。';

        $response = $this
            ->actingAs($commentedUser)
            ->post(route('items.comments.store', $item), [
                'comment' => $comment,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('item_comments', [
            'user_id' => $commentedUser->id,
            'item_id' => $item->id,
            'comment' => $comment,
        ]);
    }

    /**
     * No.9: コメント送信機能
     * ログイン前のユーザーはコメントを送信できない
     */
    public function test_guest_cannot_post_a_comment()
    {
        $item = Item::factory()->create();
        $comment = 'これはテストコメントです。';

        // コメントを送信
        $response = $this
            ->post(route('items.comments.store', $item), [
                'comment' => $comment,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('item_comments', [
            'item_id' => $item->id,
            'comment' => $comment,
        ]);
    }

    /**
     * No.9: コメント送信機能
     * コメントが入力されていない場合、バリデーションエラーが発生する
     */
    public function test_comment_is_required()
    {
        /** @var \App\Models\User $commentedUser */
        $commentedUser = User::factory()->create();
        $item = Item::factory()->create();

        // コメントを送信
        $response = $this
            ->actingAs($commentedUser)
            ->post(route('items.comments.store', $item), [
                'comment' => '',
            ]);

        $response->assertSessionHasErrors('comment');
        $errors = session('errors')->get('comment');
        $this->assertEquals('コメントを入力してください', $errors[0]);
        $this->assertDatabaseMissing('item_comments', [
            'user_id' => $commentedUser->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * No.9: コメント送信機能
     * コメントが255字以上の場合、バリデーションエラーが発生する
     */
    public function test_comment_must_not_exceed_255_characters()
    {
        /** @var \App\Models\User $commentedUser */
        $commentedUser = User::factory()->create();
        $item = Item::factory()->create();
        $comment = str_repeat('a', 256);

        // コメントを送信
        $response = $this
            ->actingAs($commentedUser)
            ->post(route('items.comments.store', $item), [
                'comment' => $comment,
            ]);

        $response->assertSessionHasErrors('comment');
        $errors = session('errors')->get('comment');
        $this->assertEquals('コメントは255文字以下で入力してください', $errors[0]);
        $this->assertDatabaseMissing('item_comments', [
            'user_id' => $commentedUser->id,
            'item_id' => $item->id,
        ]);
    }
}