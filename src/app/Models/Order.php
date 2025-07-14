<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 注文モデル。
 *
 * 商品の注文情報を管理します。
 */
class Order extends Model
{
    use HasFactory;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'buyer_id',
    ];

    /**
     * この注文の商品情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * この注文に紐づく配送先情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }
}
