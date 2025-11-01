<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\TradeMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TradeChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * US001-FN003: 別取引遷移機能
     * 取引チャット画面のサイドバーから別の取引画面に遷移する
     */
    public function test_user_can_navigate_to_other_trade_from_sidebar()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();

        // 2つの取引中の商品を作成
        $currentItem = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
            'name' => '現在の取引商品',
        ]);

        $otherItem = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
            'name' => '別の取引商品',
        ]);

        // 現在の取引チャット画面にアクセス（$currentItemで開く）
        $response = $this
            ->actingAs($buyer)
            ->get("/trade/chat/{$currentItem->id}");

        $response->assertStatus(200);
        $html = $response->getContent();

        // メインエリアに現在の取引商品が表示されていることを確認
        $this->assertStringContainsString($currentItem->name, $html);
        $this->assertStringContainsString('その他の取引', $html);

        // サイドバーに別の取引商品が表示されていることを確認
        // （$currentItemを除外した結果、$otherItemが表示される）
        $otherTradeChatUrl = "/trade/chat/{$otherItem->id}";
        $this->assertStringContainsString($otherItem->name, $html);
        $this->assertStringContainsString($otherTradeChatUrl, $html);

        // サイドバーのリンクをクリックして別の取引チャット画面に遷移できることを確認
        $otherChatResponse = $this
            ->actingAs($buyer)
            ->get($otherTradeChatUrl);

        $otherChatResponse->assertStatus(200);
        $otherChatResponse->assertSee($otherItem->name);
        // メインエリアに別の取引商品が表示されていることを確認
        $otherChatHtml = $otherChatResponse->getContent();
        $this->assertStringContainsString($otherItem->name, $otherChatHtml);

        // サイドバーには現在の商品（元々の$currentItem）が表示されることを確認
        $currentTradeChatUrl = "/trade/chat/{$currentItem->id}";
        $this->assertStringContainsString($currentItem->name, $otherChatHtml);
        $this->assertStringContainsString($currentTradeChatUrl, $otherChatHtml);
    }

    /**
     * US002-FN006: 取引チャット機能
     * 本文のみでメッセージを送信できる
     */
    public function test_user_can_send_message_with_text_only()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $response = $this
            ->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/message", [
                'message' => 'テストメッセージ',
            ]);

        $response->assertRedirect("/trade/chat/{$item->id}");
        $response->assertSessionHas('success', 'メッセージを送信しました');

        $this->assertDatabaseHas('trade_messages', [
            'item_id' => $item->id,
            'user_id' => $buyer->id,
            'message' => 'テストメッセージ',
        ]);
    }

    /**
     * US002-FN006: 取引チャット機能
     * 本文と画像でメッセージを送信できる
     */
    public function test_user_can_send_message_with_text_and_image()
    {
        Storage::fake('public');

        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $file = UploadedFile::fake()->image('test.png');

        $response = $this
            ->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/message", [
                'message' => 'テストメッセージ',
                'image' => $file,
            ]);

        $response->assertRedirect("/trade/chat/{$item->id}");
        $response->assertSessionHas('success', 'メッセージを送信しました');

        $this->assertDatabaseHas('trade_messages', [
            'item_id' => $item->id,
            'user_id' => $buyer->id,
            'message' => 'テストメッセージ',
        ]);

        // 画像が保存されているか確認
        $message = TradeMessage::where('item_id', $item->id)->first();
        $this->assertNotNull($message->image_path);
        $this->assertTrue(Storage::disk('public')->exists($message->image_path), '画像ファイルが保存されていること');
    }

    /**
     * US002-FN007, US002-FN008: バリデーション機能
     * 本文が未入力の場合、エラーメッセージが表示される
     */
    public function test_message_validation_requires_message_body()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $response = $this
            ->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/message", []);

        $response->assertSessionHasErrors('message');
        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('message') === '本文を入力してください';
        });
    }

    /**
     * US002-FN007, US002-FN008: バリデーション機能
     * 本文が401文字以上の場合、エラーメッセージが表示される
     */
    public function test_message_validation_max_length_400()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $longMessage = str_repeat('a', 401);

        $response = $this
            ->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/message", [
                'message' => $longMessage,
            ]);

        $response->assertSessionHasErrors('message');
        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('message') === '本文は400文字以内で入力してください';
        });
    }

    /**
     * US002-FN007, US002-FN008: バリデーション機能
     * 画像が.pngまたは.jpeg形式以外の場合、エラーメッセージが表示される
     */
    public function test_message_validation_image_format()
    {
        Storage::fake('public');

        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        // .gifファイルのテスト
        $fileGif = UploadedFile::fake()->image('test.gif');

        $response = $this
            ->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/message", [
                'message' => 'テストメッセージ',
                'image' => $fileGif,
            ]);

        $response->assertSessionHasErrors('image');
        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('image') === '「.png」または「.jpeg」形式でアップロードしてください';
        });

        // .svgファイルのテスト（実際の.svgファイル）
        $fileSvg = UploadedFile::fake()->create('test.svg', 100, 'image/svg+xml');

        $response2 = $this
            ->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/message", [
                'message' => 'テストメッセージ',
                'image' => $fileSvg,
            ]);

        $response2->assertSessionHasErrors('image');
        $response2->assertSessionHas('errors', function ($errors) {
            return $errors->first('image') === '「.png」または「.jpeg」形式でアップロードしてください';
        });
    }

    /**
     * US002-FN009: 入力情報保持機能
     * チャットを入力した状態で他の画面に遷移しても、入力情報を保持できる（本文のみ）
     * チャットごとに入力情報が保持されることを確認
     */
    public function test_message_input_is_preserved_when_navigating_away()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();

        // 2つの取引中の商品を作成
        $item1 = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $item2 = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $inputMessage1 = 'チャット1の入力メッセージ';
        $inputMessage2 = 'チャット2の入力メッセージ';

        // チャット1で入力した状態で他の画面に遷移したシミュレーション
        // セッションにチャット1の入力内容を保持
        $response1 = $this
            ->actingAs($buyer)
            ->withSession(['message_input_' . $item1->id => $inputMessage1])
            ->get("/trade/chat/{$item1->id}");

        $response1->assertStatus(200);
        $html1 = $response1->getContent();

        // チャット1の入力メッセージが保持されていることを確認
        $this->assertStringContainsString($inputMessage1, $html1, 'チャット1の入力メッセージが保持されていること');
        $textareaPattern1 = '/<textarea[^>]*>.*?' . preg_quote($inputMessage1, '/') . '.*?<\/textarea>/s';
        $this->assertMatchesRegularExpression($textareaPattern1, $html1, 'チャット1のtextareaに入力メッセージが保持されていること');

        // チャット2に移動して入力した状態で他の画面に遷移したシミュレーション
        // セッションにチャット1とチャット2の両方の入力内容を保持
        $response2 = $this
            ->actingAs($buyer)
            ->withSession([
                'message_input_' . $item1->id => $inputMessage1,
                'message_input_' . $item2->id => $inputMessage2,
            ])
            ->get("/trade/chat/{$item2->id}");

        $response2->assertStatus(200);
        $html2 = $response2->getContent();

        // チャット2の入力メッセージが保持されていることを確認
        $this->assertStringContainsString($inputMessage2, $html2, 'チャット2の入力メッセージが保持されていること');
        $textareaPattern2 = '/<textarea[^>]*>.*?' . preg_quote($inputMessage2, '/') . '.*?<\/textarea>/s';
        $this->assertMatchesRegularExpression($textareaPattern2, $html2, 'チャット2のtextareaに入力メッセージが保持されていること');

        // 再度チャット1に戻ったときに、チャット1の入力内容が保持されていることを確認
        $response1Again = $this
            ->actingAs($buyer)
            ->withSession([
                'message_input_' . $item1->id => $inputMessage1,
                'message_input_' . $item2->id => $inputMessage2,
            ])
            ->get("/trade/chat/{$item1->id}");

        $response1Again->assertStatus(200);
        $html1Again = $response1Again->getContent();

        // チャット1の入力メッセージが保持されていることを確認
        $this->assertStringContainsString($inputMessage1, $html1Again, 'チャット1に戻ったときに入力メッセージが保持されていること');
        // チャット2の入力メッセージは表示されていないことを確認
        $this->assertStringNotContainsString($inputMessage2, $html1Again, 'チャット1ではチャット2の入力メッセージは表示されないこと');
    }

    /**
     * US003-FN010: メッセージ編集機能
     * 投稿済みのメッセージを編集することができる
     */
    public function test_user_can_edit_own_message()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $message = TradeMessage::factory()->for($item, 'item')->for($buyer, 'user')->create([
            'message' => '元のメッセージ',
        ]);

        $response = $this
            ->actingAs($buyer)
            ->put("/trade/chat/{$item->id}/message/{$message->id}", [
                'message' => '編集したメッセージ',
            ]);

        $response->assertRedirect("/trade/chat/{$item->id}");
        $response->assertSessionHas('success', 'メッセージを更新しました');

        $this->assertDatabaseHas('trade_messages', [
            'id' => $message->id,
            'message' => '編集したメッセージ',
        ]);
    }


    /**
     * US003-FN011: メッセージ削除機能
     * 投稿済みのメッセージを削除することができる
     */
    public function test_user_can_delete_own_message()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $message = TradeMessage::factory()->for($item, 'item')->for($buyer, 'user')->create();

        $response = $this
            ->actingAs($buyer)
            ->delete("/trade/chat/{$item->id}/message/{$message->id}");

        $response->assertRedirect("/trade/chat/{$item->id}");
        $response->assertSessionHas('success', 'メッセージを削除しました');

        $this->assertDatabaseMissing('trade_messages', [
            'id' => $message->id,
        ]);
    }

    /**
     * US004-FN012: 取引後評価機能（購入者）
     * 取引を完了ボタンをクリックすると取引完了モーダルからユーザーの評価をすることができる
     */
    public function test_buyer_can_evaluate_seller_after_completing_trade()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        // 取引を完了
        $this->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/complete");

        // 評価を送信
        $response = $this
            ->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/evaluation", [
                'rating' => 5,
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success', '評価を送信しました');

        $this->assertDatabaseHas('evaluations', [
            'evaluator_id' => $buyer->id,
            'evaluated_id' => $seller->id,
            'item_id' => $item->id,
            'rating' => 5,
        ]);
    }

    /**
     * US004-FN013: 取引後評価機能（出品者）
     * 商品の購入者が取引を完了した後に、取引チャット画面を開くと取引完了モーダルからユーザーの評価をすることができる
     */
    public function test_seller_can_evaluate_buyer_after_buyer_completes_trade()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        // 購入者が取引を完了
        $this->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/complete");

        // 出品者が取引チャット画面を開くと評価モーダルが表示される（評価を送信）
        $response = $this
            ->actingAs($seller)
            ->post("/trade/chat/{$item->id}/evaluation", [
                'rating' => 4,
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success', '評価を送信しました');

        $this->assertDatabaseHas('evaluations', [
            'evaluator_id' => $seller->id,
            'evaluated_id' => $buyer->id,
            'item_id' => $item->id,
            'rating' => 4,
        ]);
    }

    /**
     * US005-FN015 US005-FN016: メール送信機能
     * 商品購入者が取引を完了すると、商品出品者宛に自動で通知メールが送信される
     */
    public function test_seller_receives_email_when_buyer_completes_trade()
    {
        \Illuminate\Support\Facades\Notification::fake();

        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $response = $this
            ->actingAs($buyer)
            ->post("/trade/chat/{$item->id}/complete");

        $response->assertRedirect("/trade/chat/{$item->id}");

        // 出品者にメールが送信されたことを確認
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $seller,
            \App\Notifications\TradeCompletedNotification::class,
            function ($notification) use ($item) {
                return $notification->item->id === $item->id;
            }
        );
    }

    // ---- 実装にあたり必要であると追加したテスト ----

    /**
     * US003-FN010: メッセージ編集機能
     * 他人のメッセージは編集できない
     */
    public function test_user_cannot_edit_other_users_message()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $message = TradeMessage::factory()->for($item, 'item')->for($seller, 'user')->create([
            'message' => '出品者のメッセージ',
        ]);

        $response = $this
            ->actingAs($buyer)
            ->put("/trade/chat/{$item->id}/message/{$message->id}", [
                'message' => '編集しようとしたメッセージ',
            ]);

        $response->assertForbidden();
    }

    /**
     * US003-FN011: メッセージ削除機能
     * 他人のメッセージは削除できない
     */
    public function test_user_cannot_delete_other_users_message()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();
        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $message = TradeMessage::factory()->for($item, 'item')->for($seller, 'user')->create();

        $response = $this
            ->actingAs($buyer)
            ->delete("/trade/chat/{$item->id}/message/{$message->id}");

        $response->assertForbidden();
    }
}

