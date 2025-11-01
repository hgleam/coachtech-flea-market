<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\TradeMessage;
use App\Models\Evaluation;
use App\Http\Requests\TradeMessageRequest;
use App\Http\Requests\EvaluationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 取引チャットコントローラー
 *
 * 取引チャット画面の表示、メッセージ送信、取引完了処理を行います。
 */
class TradeChatController extends Controller
{
    /**
     * 取引チャット画面を表示します。
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Item $item)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$item->isRelatedTo($user)) {
            return redirect()->route('items.index')->with('error', 'この取引にアクセスする権限がありません');
        }

        if (!$item->isTradeAccessible()) {
            return redirect()->route('items.show', $item)->with('error', 'この商品は取引中または取引完了ではありません');
        }

        $user->markItemChatAsRead($item);

        return view('trade.chat', compact('item'));
    }

    /**
     * メッセージを送信します。
     *
     * @param  \App\Http\Requests\TradeMessageRequest  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeMessage(TradeMessageRequest $request, Item $item)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$item->isRelatedTo($user)) {
            return redirect()->route('items.index')->with('error', 'この取引にアクセスする権限がありません');
        }

        if (!$item->isTrading()) {
            return redirect()->route('trade.chat', $item)->with('error', '取引が完了しているため、メッセージを送信できません');
        }

        TradeMessage::createWithImage([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ], $request->file('image'));

        return redirect()->route('trade.chat', $item)
            ->with('success', 'メッセージを送信しました')
            ->without('message_input_' . $item->id);
    }

    /**
     * 取引を完了します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeTrade(Request $request, Item $item)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$item->isRelatedTo($user)) {
            return redirect()->route('items.index')->with('error', 'この取引にアクセスする権限がありません');
        }

        if (!$item->isTrading()) {
            return redirect()->route('trade.chat', $item)->with('error', 'この商品は取引中ではありません');
        }

        try {
            $item->completeTradeBy($user);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '取引完了処理中にエラーが発生しました。もう一度お試しください。');
        }

        return redirect()->route('trade.chat', $item)->with('success', '取引を完了しました')->with('needs_evaluation', true);
    }

    /**
     * 評価を送信します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeEvaluation(EvaluationRequest $request, Item $item)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$item->isRelatedTo($user)) {
            return redirect()->route('items.index')->with('error', 'この取引にアクセスする権限がありません');
        }

        if (!$item->isCompleted()) {
            return redirect()->route('trade.chat', $item)->with('error', '取引が完了していないため、評価を送信できません');
        }

        $evaluated = $item->getTradePartner($user);

        if (Evaluation::hasAlreadyEvaluated($user, $evaluated, $item)) {
            return redirect()->route('trade.chat', $item)->with('error', '既に評価を送信しています');
        }

        Evaluation::createForTrade($user, $evaluated, $item, $request->rating);

        return redirect()->route('items.index')->with('success', '評価を送信しました');
    }

    /**
     * メッセージを更新します。
     *
     * @param  \App\Http\Requests\TradeMessageRequest  $request
     * @param  \App\Models\Item  $item
     * @param  \App\Models\TradeMessage  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMessage(TradeMessageRequest $request, Item $item, TradeMessage $message)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$message->isOwnedBy($user)) {
            abort(403, 'このメッセージを編集する権限がありません');
        }

        if (!$message->isRelatedToItem($item)) {
            abort(404);
        }

        $message->update([
            'message' => $request->message,
        ]);

        return redirect()->route('trade.chat', $item)->with('success', 'メッセージを更新しました');
    }

    /**
     * メッセージを削除します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item  $item
     * @param  \App\Models\TradeMessage  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyMessage(Request $request, Item $item, TradeMessage $message)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$message->isOwnedBy($user)) {
            abort(403, 'このメッセージを削除する権限がありません');
        }

        if (!$message->isRelatedToItem($item)) {
            abort(404);
        }

        $message->delete();

        return redirect()->route('trade.chat', $item)->with('success', 'メッセージを削除しました');
    }
}
