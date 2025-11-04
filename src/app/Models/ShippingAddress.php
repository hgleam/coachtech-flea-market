<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 配送先情報モデル。
 *
 * 注文に紐づく配送先の住所情報を管理します。
 */
class ShippingAddress extends Model
{
    use HasFactory;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array<string>
     */
    protected $fillable = [
        'order_id',
        'zip_code',
        'address',
        'building',
    ];

    /**
     * この配送先情報が紐づく注文情報を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
