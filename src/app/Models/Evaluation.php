<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 評価モデル。
 *
 * 取引後のユーザー評価を管理します。
 */
class Evaluation extends Model
{
    use HasFactory;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array
     */
    protected $fillable = [
        'evaluator_id',
        'evaluated_id',
        'item_id',
        'rating',
    ];

    /**
     * 評価者の情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * 被評価者の情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evaluated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_id');
    }

    /**
     * 取引商品の情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * 指定されたユーザーが指定された取引商品で既に評価を送信しているかどうかを判定します。
     *
     * @param  \App\Models\User  $evaluator
     * @param  \App\Models\User  $evaluated
     * @param  \App\Models\Item  $item
     * @return bool
     */
    public static function hasAlreadyEvaluated(User $evaluator, User $evaluated, Item $item): bool
    {
        return static::where('evaluator_id', $evaluator->id)
            ->where('evaluated_id', $evaluated->id)
            ->where('item_id', $item->id)
            ->exists();
    }

    /**
     * 取引完了後の評価を作成します。
     *
     * @param  \App\Models\User  $evaluator  評価者
     * @param  \App\Models\User  $evaluated  被評価者
     * @param  \App\Models\Item  $item  取引商品
     * @param  int  $rating  評価値（1-5）
     * @return \App\Models\Evaluation
     */
    public static function createForTrade(User $evaluator, User $evaluated, Item $item, int $rating): self
    {
        return static::create([
            'evaluator_id' => $evaluator->id,
            'evaluated_id' => $evaluated->id,
            'item_id' => $item->id,
            'rating' => $rating,
        ]);
    }
}
