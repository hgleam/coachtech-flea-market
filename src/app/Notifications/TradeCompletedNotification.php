<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 取引完了通知。
 *
 * 商品購入者が取引を完了した際に、出品者宛に送信される通知メールを提供します。
 */
class TradeCompletedNotification extends Notification
{
    use Queueable;

    /**
     * 取引が完了した商品
     *
     * @var \App\Models\Item
     */
    public $item;

    /**
     * 新しい通知インスタンスを作成します。
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * 通知の配信チャンネルを取得します。
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * メールの表現を取得します。
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('取引が完了しました')
            ->line('「' . $this->item->name . '」の取引が完了しました。')
            ->line('購入者との取引チャット画面から、取引相手の評価を行うことができます。')
            ->action('取引チャットを確認する', route('trade.chat', $this->item))
            ->line('ご利用ありがとうございました。');
    }

    /**
     * 通知の配列表現を取得します。
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
        ];
    }
}
