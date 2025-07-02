<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * いいね（お気に入り）モデル。
 *
 * ユーザーと商品の「いいね」リレーションを管理します。
 */
class LikedItem extends Model
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
    ];
}
