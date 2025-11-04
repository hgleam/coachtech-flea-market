<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\ItemStatus;
use App\Notifications\TradeCompletedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * 商品モデル。
 *
 * 商品の情報を管理します。
 */
class Item extends Model
{
    use HasFactory;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array
     */
    protected $fillable = [
        'seller_id',
        'buyer_id',
        'condition',
        'name',
        'brand_name',
        'description',
        'price',
        'image_path',
        'status',
    ];

    /**
     * ネイティブな型にキャストする属性。
     *
     * @var array
     */
    protected $casts = [
        'condition' => ItemCondition::class,
        'status' => ItemStatus::class,
    ];

    /**
     * この商品に紐づく注文情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /**
     * この商品にいいねをしたユーザーを取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'liked_items');
    }

    /**
     * この商品に対するコメントを取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ItemComment::class);
    }

    /**
     * この商品のカテゴリを取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'item_categories');
    }

    /**
     * この商品の出品者情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * この商品の購入者情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * この商品に紐づく取引メッセージを取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tradeMessages(): HasMany
    {
        return $this->hasMany(TradeMessage::class);
    }

    /**
     * この商品の取引メッセージをユーザー情報付きで時系列順に取得します。
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrderedTradeMessages()
    {
        return $this->tradeMessages()->with('user')->orderBy('created_at', 'asc')->get();
    }

    /**
     * この商品の取引メッセージ件数を取得します。
     *
     * @return int
     */
    public function getTradeMessageCount(): int
    {
        return $this->tradeMessages()->count();
    }

    /**
     * 指定されたユーザーにとっての未読メッセージ件数を取得します。
     * 未読メッセージは、ユーザーが最後にチャット画面を開いた時刻より後に、
     * 相手から送信されたメッセージです。
     *
     * @param  \App\Models\User  $user
     * @return int
     */
    public function getUnreadMessageCount(User $user): int
    {
        $tradePartner = $this->getTradePartner($user);
        if (! $tradePartner) {
            return 0;
        }

        // ユーザーが最後にチャット画面を開いた時刻を取得
        $lastRead = DB::table('user_item_last_read')
            ->where('user_id', $user->id)
            ->where('item_id', $this->id)
            ->value('last_read_at');

        // last_read_atがnullの場合、商品作成時刻を基準にする
        $since = $lastRead ? \Carbon\Carbon::parse($lastRead) : $this->created_at;

        // 相手からのメッセージで、last_read_atより新しいものをカウント
        return $this->tradeMessages()
            ->where('user_id', $tradePartner->id)
            ->where('created_at', '>', $since)
            ->count();
    }

    /**
     * この商品の最新メッセージの作成日時を取得します。
     * メッセージがない場合は、商品の作成日時を返します。
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getLatestTradeMessageDate()
    {
        $latestMessage = $this->tradeMessages()->latest('created_at')->first();

        return $latestMessage ? $latestMessage->created_at : $this->created_at;
    }

    /**
     * 未読メッセージがあるかどうかを判定します。
     * unread_countプロパティが設定されている場合はそれを使用します。
     * 設定されていない場合は、指定されたユーザーに対して未読メッセージ件数を取得して判定します。
     *
     * @param  \App\Models\User|null  $user 未読件数を取得するユーザー（unread_countが設定されていない場合のみ使用）
     * @return bool
     */
    public function hasUnreadMessages(?User $user = null): bool
    {
        // unread_countプロパティが既に設定されている場合はそれを使用
        if (isset($this->unread_count)) {
            return $this->unread_count > 0;
        }

        // unread_countが設定されていない場合、ユーザーを指定して取得
        if ($user) {
            return $this->getUnreadMessageCount($user) > 0;
        }

        return false;
    }

    /**
     * 指定されたユーザーがこの商品に関連しているかどうかを判定します。
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function isRelatedTo(User $user): bool
    {
        return $this->seller_id === $user->id || $this->buyer_id === $user->id;
    }

    /**
     * 指定されたユーザーがこの商品の出品者かどうかを判定します。
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function isSeller(User $user): bool
    {
        return $this->seller_id === $user->id;
    }

    /**
     * この商品の取引相手のユーザーを取得します。
     *
     * @param  \App\Models\User  $user
     * @return \App\Models\User|null
     */
    public function getTradePartner(User $user): ?User
    {
        return $this->isSeller($user) ? $this->buyer : $this->seller;
    }

    /**
     * この商品が取引中かどうかを判定します。
     *
     * @return bool
     */
    public function isTrading(): bool
    {
        return $this->status === ItemStatus::TRADING;
    }

    /**
     * この商品が取引完了済みかどうかを判定します。
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === ItemStatus::COMPLETED;
    }

    /**
     * この商品が取引チャット画面にアクセス可能かどうかを判定します。
     *
     * @return bool
     */
    public function isTradeAccessible(): bool
    {
        return $this->isTrading() || $this->isCompleted();
    }

    /**
     * 指定されたユーザーがこの商品の取引相手を評価する必要があるかどうかを判定します。
     *
     * @param  \App\Models\User  $user
     * @return array{needs: bool, target: \App\Models\User|null}
     */
    public function needsEvaluationBy(User $user): array
    {
        if (! $this->isCompleted()) {
            return ['needs' => false, 'target' => null];
        }

        $evaluationTarget = $this->getTradePartner($user);
        if (! $evaluationTarget) {
            return ['needs' => false, 'target' => null];
        }

        $existingEvaluation = \App\Models\Evaluation::where('evaluator_id', $user->id)
            ->where('evaluated_id', $evaluationTarget->id)
            ->where('item_id', $this->id)
            ->first();

        return [
            'needs' => ! $existingEvaluation,
            'target' => $evaluationTarget,
        ];
    }

    /**
     * 指定されたユーザーによって取引を完了します。
     * 購入者が取引を完了した場合、出品者に通知メールを送信します。
     *
     * @param  \App\Models\User  $user
     * @return void
     * @throws \Exception
     */
    public function completeTradeBy(User $user): void
    {
        if (! $this->isTrading()) {
            throw new \RuntimeException('この商品は取引中ではありません');
        }

        DB::transaction(function () use ($user) {
            $this->update(['status' => ItemStatus::COMPLETED->value]);

            // 商品購入者が取引を完了すると、商品出品者宛に自動で通知メールが送信される
            $isSeller = $this->isSeller($user);
            if (! $isSeller && $this->seller) {
                $this->seller->notify(new TradeCompletedNotification($this));
            }
        });
    }
}
