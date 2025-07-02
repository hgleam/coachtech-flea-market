<?php

namespace App\Models;

use App\Enums\ItemCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'condition',
        'name',
        'brand_name',
        'description',
        'price',
        'image_path',
    ];

    /**
     * ネイティブな型にキャストする属性。
     *
     * @var array
     */
    protected $casts = [
        'condition' => ItemCondition::class,
    ];
}
