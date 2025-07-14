<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 商品コメントモデル。
 *
 * 商品に紐づくコメントを管理します。
 */
class ItemComment extends Model
{
    use HasFactory;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'user_id',
        'comment',
    ];

    /**
     * このコメントを投稿したユーザーを取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
