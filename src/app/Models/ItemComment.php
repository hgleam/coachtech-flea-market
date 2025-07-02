<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
