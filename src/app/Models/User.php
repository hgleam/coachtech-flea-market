<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

/**
 * ユーザーモデル。
 *
 * アプリケーションのユーザー情報を管理します。
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image_path',
        'zip_code',
        'address',
        'building',
    ];

    /**
     * 配列やJSONに含めない（隠す）属性。
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * ネイティブな型にキャストする属性。
     *
     * @var array<string, string>
     */
    protected $casts = [
    ];

    /**
     * ユーザーが出品した商品を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function soldItems(): HasMany
    {
        return $this->hasMany(Item::class, 'seller_id');
    }

    /**
     * ユーザーが購入した注文を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchasedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * ユーザーが購入した商品を中間テーブル(orders)経由で取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function purchasedItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            Item::class,
            Order::class,
            'buyer_id', // ordersテーブルの外部キー
            'id',       // itemsテーブルの外部キー
            'id',       // usersテーブルのローカルキー
            'item_id'   // ordersテーブルのローカルキー
        );
    }

    /**
     * ユーザーがいいねした商品を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedItems(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'liked_items');
    }

    /**
     * ユーザーが出品した取引中の商品を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tradingItemsAsSeller(): HasMany
    {
        return $this->hasMany(Item::class, 'seller_id')
            ->where('status', ItemStatus::TRADING->value)
            ->whereNotNull('buyer_id');
    }

    /**
     * ユーザーが購入した取引中の商品を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tradingItemsAsBuyer(): HasMany
    {
        return $this->hasMany(Item::class, 'buyer_id')
            ->where('status', ItemStatus::TRADING->value);
    }

    /**
     * ユーザーが評価した評価一覧を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evaluationsGiven(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    /**
     * ユーザーが受け取った評価一覧を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evaluationsReceived(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluated_id');
    }

    /**
     * 指定された商品のチャット画面を開いた時刻を記録します。
     * 未読判定の基準時刻として使用されます。
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function markItemChatAsRead(Item $item): void
    {
        $now = Carbon::now();
        $exists = DB::table('user_item_last_read')
            ->where('user_id', $this->id)
            ->where('item_id', $item->id)
            ->exists();

        if ($exists) {
            DB::table('user_item_last_read')
                ->where('user_id', $this->id)
                ->where('item_id', $item->id)
                ->update([
                    'last_read_at' => $now,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('user_item_last_read')->insert([
                'user_id' => $this->id,
                'item_id' => $item->id,
                'last_read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * 指定された商品を除く、その他の取引中の商品を取得します。
     * 各商品に未読メッセージ件数を含めます。
     *
     * @param  \App\Models\Item  $excludeItem
     * @return \Illuminate\Support\Collection<int, \App\Models\Item>
     */
    public function getTradingItemsExcept(Item $excludeItem)
    {
        return $this->tradingItemsAsSeller->merge($this->tradingItemsAsBuyer)
            ->filter(function ($tradingItem) use ($excludeItem) {
                return $tradingItem->id !== $excludeItem->id && $tradingItem->isTrading();
            })
            ->unique('id')
            ->map(function ($item) {
                $item->unread_count = $item->getUnreadMessageCount($this);

                return $item;
            });
    }

    /**
     * 取引中の商品を最新メッセージ順にソートして取得します。
     * 各商品にメッセージ件数、未読メッセージ件数、最新メッセージ日時を付与します。
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Item>
     */
    public function getTradingItemsSortedByLatestMessage()
    {
        $items = $this->tradingItemsAsSeller->merge($this->tradingItemsAsBuyer)->unique('id');

        return $items->map(function ($item) {
            $item->message_count = $item->getTradeMessageCount();
            $item->unread_count = $item->getUnreadMessageCount($this);
            $item->latest_message_at = $item->getLatestTradeMessageDate();

            return $item;
        })->sortByDesc(function ($item) {
            $date = $item->latest_message_at;

            return $date instanceof Carbon ? $date->timestamp : strtotime($date);
        })->values();
    }

    /**
     * このユーザーが受け取った評価の平均値を取得します。
     * 評価がない場合は null を返します。
     * 平均値に小数がある場合は整数に四捨五入します。
     *
     * @return int|null
     */
    public function getAverageRating(): ?int
    {
        $evaluations = $this->evaluationsReceived;
        if ($evaluations->isEmpty()) {
            return null;
        }

        return (int) round($evaluations->avg('rating'));
    }

    /**
     * 平均評価値のアクセサ。
     * $user->averageRating でアクセスできます。
     *
     * @return int|null
     */
    public function getAverageRatingAttribute(): ?int
    {
        return $this->getAverageRating();
    }

    /**
     * 取引中の商品の件数を取得します。
     *
     * @return int
     */
    public function getTradingCount(): int
    {
        return $this->getTradingItemsSortedByLatestMessage()->count();
    }

    /**
     * 取引中の商品の件数のアクセサ。
     * $user->tradingCount でアクセスできます。
     *
     * @return int
     */
    public function getTradingCountAttribute(): int
    {
        return $this->getTradingCount();
    }
}
