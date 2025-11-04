<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * 取引メッセージモデル。
 *
 * 取引チャットのメッセージ情報を管理します。
 */
class TradeMessage extends Model
{
    use HasFactory;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array<string>
     */
    protected $fillable = [
        'item_id',
        'user_id',
        'message',
        'image_path',
    ];

    /**
     * このメッセージが紐づく商品情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * このメッセージの送信者情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 指定されたユーザーがこのメッセージの所有者かどうかを判定します。
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * このメッセージが指定された商品に紐づいているかどうかを判定します。
     *
     * @param  \App\Models\Item  $item
     * @return bool
     */
    public function isRelatedToItem(Item $item): bool
    {
        return $this->item_id === $item->id;
    }

    /**
     * メッセージと画像を作成します。
     *
     * @param  array<string, mixed>  $data
     * @param  \Illuminate\Http\UploadedFile|null  $image
     * @return self
     */
    public static function createWithImage(array $data, ?UploadedFile $image = null): self
    {
        if ($image) {
            $data['image_path'] = $image->store('trade_messages', 'public');
        }

        return static::create($data);
    }

    /**
     * モデルのブート処理を行います。
     * 削除時に画像ファイルも削除します。
     */
    protected static function booted(): void
    {
        static::deleting(function (TradeMessage $message) {
            if ($message->image_path && Storage::disk('public')->exists($message->image_path)) {
                Storage::disk('public')->delete($message->image_path);
            }
        });
    }
}
