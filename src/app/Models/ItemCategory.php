<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 商品カテゴリ（中間テーブル）モデル。
 *
 * 商品とカテゴリの多対多リレーションを管理します。
 */
class ItemCategory extends Model
{
    use HasFactory;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'category_id',
    ];
}
