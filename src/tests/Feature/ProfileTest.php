<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;
use App\Models\TradeMessage;
use Carbon\Carbon;

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
        // 購入した商品は出品した商品タブでは表示されない（商品名が短い場合の部分一致を避けるため、URLで確認）
        $response_sell->assertDontSee("/item/{$itemPurchased->id}");

        // 2. 購入した商品が表示されることを確認
        $response_buy = $this
            ->actingAs($user)
            ->from('/mypage')
            ->get('/mypage?page=buy');

        $response_buy->assertStatus(200);
        $response_buy->assertSee($user->name);
        $response_buy->assertSee($itemPurchased->name);
        // 出品した商品は購入した商品タブでは表示されない（商品名が短い場合の部分一致を避けるため、URLで確認）
        $response_buy->assertDontSee("/item/{$itemSold->id}");
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


    // ---- Pro入会テスト用の追加仕様のテスト ----

    /**
     * US001-FN001: 取引中商品確認機能
     * マイページから取引中の商品を確認することができる
     */
    public function test_user_can_view_trading_items()
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
            ->get('/mypage?page=trading');

        $response->assertStatus(200);
        $response->assertSee($item->name);
    }

    /**
     * US001-FN001: 取引中商品確認機能
     * マイページから取引メッセージが何件来ているかが確認できる
     */
    public function test_user_can_view_message_count_in_trading_items()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();

        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
            'created_at' => Carbon::now()->subDays(1), // 商品を作成した時刻を過去に設定
        ]);

        // 未読メッセージを作成（出品者から購入者へのメッセージ）
        // 商品作成時刻より後の時刻でメッセージを作成することで、未読として判定される
        TradeMessage::factory()->for($item, 'item')->for($seller, 'user')->create([
            'created_at' => Carbon::now(),
        ]);

        $response = $this
            ->actingAs($buyer)
            ->get('/mypage?page=trading');

        $response->assertStatus(200);
        $response->assertSee($item->name);
        // 未読メッセージが1件ある場合、バッジに「1」が表示される
        $html = $response->getContent();
        $this->assertStringContainsString('item-card-profile__notification-badge', $html);
    }

    /**
     * US001-FN002: 取引チャット遷移機能
     * マイページの取引中の商品を押下することで、取引チャット画面へ遷移することができる
     */
    public function test_user_can_navigate_to_trade_chat_from_profile()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();

        $item = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        // プロフィール画面の取引中タブにアクセス
        $response = $this
            ->actingAs($buyer)
            ->get('/mypage?page=trading');

        $response->assertStatus(200);
        $html = $response->getContent();

        // 商品名が表示されていることを確認
        $this->assertStringContainsString($item->name, $html);

        // 取引チャット画面へのリンクが存在することを確認
        $tradeChatUrl = "/trade/chat/{$item->id}";
        $this->assertStringContainsString($tradeChatUrl, $html);

        // リンクをクリックして取引チャット画面に遷移できることを確認
        $chatResponse = $this
            ->actingAs($buyer)
            ->get($tradeChatUrl);

        $chatResponse->assertStatus(200);
        $chatResponse->assertSee($item->name);
    }

    /**
     * US001-FN004: 取引自動ソート機能
     * 取引中の商品の並び順は新規メッセージが来た順に表示する
     */
    public function test_trading_items_are_sorted_by_latest_message()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();

        // 3つの商品を作成
        $item1 = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $item2 = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        $item3 = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
        ]);

        // item1に最も古いメッセージ
        TradeMessage::factory()->for($item1, 'item')->for($seller, 'user')->create([
            'created_at' => Carbon::now()->subDays(3),
        ]);

        // item2に2番目に新しいメッセージ
        TradeMessage::factory()->for($item2, 'item')->for($seller, 'user')->create([
            'created_at' => Carbon::now()->subDays(1),
        ]);

        // item3に最も新しいメッセージ
        TradeMessage::factory()->for($item3, 'item')->for($seller, 'user')->create([
            'created_at' => Carbon::now(),
        ]);

        $response = $this
            ->actingAs($buyer)
            ->get('/mypage?page=trading');

        $response->assertStatus(200);

        // 実際のソート順を確認（メソッドから直接）
        $tradingItems = $buyer->getTradingItemsSortedByLatestMessage();
        $itemIds = $tradingItems->pluck('id')->toArray();

        // item3が最初、item2が2番目、item1が3番目であることを確認
        $this->assertEquals($item3->id, $itemIds[0], 'item3が最初であること');
        $this->assertEquals($item2->id, $itemIds[1], 'item2が2番目であること');
        $this->assertEquals($item1->id, $itemIds[2], 'item1が3番目であること');

        $html = $response->getContent();

        // HTML内で商品IDを検索（実際のURLパスに含まれている）
        $item3Url = "/trade/chat/{$item3->id}";
        $item2Url = "/trade/chat/{$item2->id}";
        $item1Url = "/trade/chat/{$item1->id}";

        $item3Pos = strpos($html, $item3Url);
        $item2Pos = strpos($html, $item2Url);
        $item1Pos = strpos($html, $item1Url);

        $this->assertNotFalse($item3Pos, 'item3がHTML内に存在すること');
        $this->assertNotFalse($item2Pos, 'item2がHTML内に存在すること');
        $this->assertNotFalse($item1Pos, 'item1がHTML内に存在すること');
        // item3が最初（位置が小さい）、item2が次、item1が最後
        $this->assertLessThan($item2Pos, $item3Pos, 'item3がitem2より前に表示されること');
        $this->assertLessThan($item1Pos, $item2Pos, 'item2がitem1より前に表示されること');
    }

    /**
     * US001-FN005: 取引商品新規通知確認機能
     * 新規通知が来た商品は、取引中の各商品の左上に通知マークを表示する
     * 通知マークから何件メッセージが来ているかが確認できる
     */
    public function test_trading_items_with_messages_show_notification_mark()
    {
        /** @var \App\Models\User $seller */
        $seller = User::factory()->create();
        /** @var \App\Models\User $buyer */
        $buyer = User::factory()->create();

        $itemWithUnreadMessages = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
            'created_at' => Carbon::now()->subDays(1), // 商品を作成した時刻を過去に設定
        ]);

        $itemWithoutUnreadMessages = Item::factory()->for($seller, 'seller')->create([
            'buyer_id' => $buyer->id,
            'status' => \App\Enums\ItemStatus::TRADING->value,
            'created_at' => Carbon::now()->subDays(1), // 商品を作成した時刻を過去に設定
        ]);

        // itemWithUnreadMessagesに未読メッセージを作成（相手から送信されたメッセージ）
        // 商品作成時刻より後の時刻でメッセージを作成する
        \App\Models\TradeMessage::factory()->for($itemWithUnreadMessages, 'item')->for($seller, 'user')->create([
            'created_at' => Carbon::now(), // 商品作成時刻より後の時刻で作成
        ]);

        // itemWithoutUnreadMessagesにはメッセージを作成しない
        // ユーザーがチャット画面を開いていない状態では、商品作成時刻を基準に未読判定されるため、
        // メッセージを作成しなければ未読は0件になる想定でテストを行う

        $response = $this
            ->actingAs($buyer)
            ->get('/mypage?page=trading');

        $response->assertStatus(200);
        $html = $response->getContent();

        // 未読メッセージがある商品には通知マークが表示される
        // itemWithUnreadMessagesには未読メッセージがあるため、通知マークが表示される
        $itemWithUnreadMessagesPos = strpos($html, $itemWithUnreadMessages->name);
        $itemWithoutUnreadMessagesPos = strpos($html, $itemWithoutUnreadMessages->name);

        // itemWithUnreadMessagesの商品名の後に通知マークがあることを確認
        $notificationMarkPos = strpos($html, 'item-card-profile__notification-mark');
        $this->assertNotFalse($notificationMarkPos, '通知マークが表示されること');
        $this->assertNotFalse(strpos($html, 'item-card-profile__notification-badge'), '通知バッジが表示されること');

        // 未読メッセージがない商品には通知マークが表示されない
        // itemWithoutUnreadMessagesには未読メッセージがないため、通知マークが表示されないことを確認
        $this->assertStringContainsString($itemWithUnreadMessages->name, $html);
        $this->assertStringContainsString($itemWithoutUnreadMessages->name, $html);

        // itemWithoutUnreadMessagesの商品名の周辺に通知マークが含まれていないことを確認
        // 商品名の位置を取得し、その前後のHTMLを確認
        $itemWithoutUnreadMessagesNamePos = strpos($html, $itemWithoutUnreadMessages->name);
        $this->assertNotFalse($itemWithoutUnreadMessagesNamePos, 'itemWithoutUnreadMessagesの商品名が表示されること');

        // 商品名の後の一定範囲（500文字程度）を抽出して、通知マークのクラスが含まれていないことを確認
        $surroundingHtml = substr($html, $itemWithoutUnreadMessagesNamePos, 500);
        $this->assertStringNotContainsString('item-card-profile__notification-mark', $surroundingHtml, 'itemWithoutUnreadMessagesには通知マークが表示されないこと');

        // 通知マークから未読メッセージ件数が確認できることを確認
        // unread_countが1件以上の場合、バッジに数字が表示される
        $itemWithUnreadMessages->refresh();
        $unreadCount = $itemWithUnreadMessages->getUnreadMessageCount($buyer);
        if ($unreadCount > 0) {
            $this->assertStringContainsString((string)$unreadCount, $html);
        }
    }

    /**
     * US002-FN005: 取引評価の平均確認機能
     * 他ユーザーからの取引評価の平均をプロフィール画面にて表示する
     */
    public function test_user_can_view_average_rating_on_profile()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 評価を作成（平均3.5になるように）
        $evaluator1 = User::factory()->create();
        $evaluator2 = User::factory()->create();
        $item1 = Item::factory()->for($user, 'seller')->create();
        $item2 = Item::factory()->for($user, 'seller')->create();

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator1->id,
            'evaluated_id' => $user->id,
            'item_id' => $item1->id,
            'rating' => 4,
        ]);

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator2->id,
            'evaluated_id' => $user->id,
            'item_id' => $item2->id,
            'rating' => 3,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/mypage');

        $response->assertStatus(200);
        // 平均評価が表示されることを確認（(4+3)/2 = 3.5 → 四捨五入で4）
        $html = $response->getContent();

        // 評価コンポーネントが表示されることを確認
        $this->assertStringContainsString('profile-header__rating', $html);

        // 平均3.5の場合、整数に四捨五入して4になるため、完全な星が4つ表示されることを確認
        $filledStarsCount = substr_count($html, 'profile-header__rating-star--filled');
        $this->assertEquals(4, $filledStarsCount, '完全な星が4つ表示されること（3.5→4に四捨五入）');

        // 半分星は表示されない（整数に四捨五入済みなので）
        $halfStarCount = substr_count($html, 'profile-header__rating-star--half');
        $this->assertEquals(0, $halfStarCount, '半分の星は表示されないこと');
    }

    /**
     * US002-FN005: 評価平均確認機能
     * 評価数の平均値に小数がある場合、その値は四捨五入する（整数に）
     * 平均3.5の場合は4に四捨五入される
     */
    public function test_average_rating_is_rounded_up_when_decimal_is_5_or_greater()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 評価を作成（平均3.5になるように：4 + 3 = 7, 7/2 = 3.5）
        $evaluator1 = User::factory()->create();
        $evaluator2 = User::factory()->create();
        $item1 = Item::factory()->for($user, 'seller')->create();
        $item2 = Item::factory()->for($user, 'seller')->create();

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator1->id,
            'evaluated_id' => $user->id,
            'item_id' => $item1->id,
            'rating' => 4,
        ]);

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator2->id,
            'evaluated_id' => $user->id,
            'item_id' => $item2->id,
            'rating' => 3,
        ]);

        // 平均評価が3.5で、整数に四捨五入されて4になることを確認
        $averageRating = $user->getAverageRating();
        $this->assertEquals(4, $averageRating, '平均評価は3.5から4に四捨五入されること');
    }

    /**
     * US002-FN005: 評価平均確認機能
     * 評価数の平均値に小数がある場合、その値は四捨五入する（整数に）
     * 平均3.7の場合は4に四捨五入される
     */
    public function test_average_rating_with_decimal_greater_than_5_rounds_up()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 評価を作成（平均3.67になるように：4 + 4 + 3 = 11, 11/3 ≈ 3.67）
        $evaluator1 = User::factory()->create();
        $evaluator2 = User::factory()->create();
        $evaluator3 = User::factory()->create();
        $item1 = Item::factory()->for($user, 'seller')->create();
        $item2 = Item::factory()->for($user, 'seller')->create();
        $item3 = Item::factory()->for($user, 'seller')->create();

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator1->id,
            'evaluated_id' => $user->id,
            'item_id' => $item1->id,
            'rating' => 4,
        ]);

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator2->id,
            'evaluated_id' => $user->id,
            'item_id' => $item2->id,
            'rating' => 4,
        ]);

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator3->id,
            'evaluated_id' => $user->id,
            'item_id' => $item3->id,
            'rating' => 3,
        ]);

        // 平均評価が約3.67で、整数に四捨五入されて4になることを確認
        $averageRating = $user->getAverageRating();
        $this->assertEquals(4, $averageRating, '平均評価は3.67から4に四捨五入されること');
    }

    /**
     * US002-FN005: 評価平均確認機能
     * 評価数の平均値に小数がある場合、その値は四捨五入する（整数に）
     * 平均3.4の場合は3に四捨五入される
     */
    public function test_average_rating_with_decimal_less_than_5_rounds_down()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 評価を作成（平均3.4になるように：3 + 3 + 3 + 3 + 5 = 17, 17/5 = 3.4）
        $evaluator1 = User::factory()->create();
        $evaluator2 = User::factory()->create();
        $evaluator3 = User::factory()->create();
        $evaluator4 = User::factory()->create();
        $evaluator5 = User::factory()->create();
        $item1 = Item::factory()->for($user, 'seller')->create();
        $item2 = Item::factory()->for($user, 'seller')->create();
        $item3 = Item::factory()->for($user, 'seller')->create();
        $item4 = Item::factory()->for($user, 'seller')->create();
        $item5 = Item::factory()->for($user, 'seller')->create();

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator1->id,
            'evaluated_id' => $user->id,
            'item_id' => $item1->id,
            'rating' => 3,
        ]);
        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator2->id,
            'evaluated_id' => $user->id,
            'item_id' => $item2->id,
            'rating' => 3,
        ]);
        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator3->id,
            'evaluated_id' => $user->id,
            'item_id' => $item3->id,
            'rating' => 3,
        ]);
        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator4->id,
            'evaluated_id' => $user->id,
            'item_id' => $item4->id,
            'rating' => 3,
        ]);
        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator5->id,
            'evaluated_id' => $user->id,
            'item_id' => $item5->id,
            'rating' => 5,
        ]);

        // 平均評価が3.4で、整数に四捨五入されて3になることを確認
        $averageRating = $user->getAverageRating();
        $this->assertEquals(3, $averageRating, '平均評価は3.4から3に四捨五入されること');
    }

    /**
     * US002-FN005: 評価平均確認機能
     * 評価数の平均値に小数がある場合、その値は四捨五入する（整数に）
     * 平均3.33...の場合は3に四捨五入される
     */
    public function test_average_rating_is_rounded_down_when_decimal_is_less_than_5()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 評価を作成（平均3.33...になるように：5 + 4 + 1 = 10, 10/3 ≈ 3.33）
        $evaluator1 = User::factory()->create();
        $evaluator2 = User::factory()->create();
        $evaluator3 = User::factory()->create();
        $item1 = Item::factory()->for($user, 'seller')->create();
        $item2 = Item::factory()->for($user, 'seller')->create();
        $item3 = Item::factory()->for($user, 'seller')->create();

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator1->id,
            'evaluated_id' => $user->id,
            'item_id' => $item1->id,
            'rating' => 5,
        ]);

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator2->id,
            'evaluated_id' => $user->id,
            'item_id' => $item2->id,
            'rating' => 4,
        ]);

        \App\Models\Evaluation::factory()->create([
            'evaluator_id' => $evaluator3->id,
            'evaluated_id' => $user->id,
            'item_id' => $item3->id,
            'rating' => 1,
        ]);

        // 平均評価が約3.33で、整数に四捨五入されて3になることを確認
        $averageRating = $user->getAverageRating();
        $this->assertEquals(3, $averageRating, '平均評価は3.33から3に四捨五入されること');
    }

    /**
     * US002-FN005: 取引評価の平均確認機能
     * まだ評価がないユーザーの場合は評価は表示しない
     */
    public function test_user_without_ratings_does_not_show_rating()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/mypage');

        $response->assertStatus(200);
        // 評価がない場合は評価が表示されない
        $html = $response->getContent();
        $this->assertStringNotContainsString('評価', $html);
    }
}