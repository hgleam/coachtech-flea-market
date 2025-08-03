<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Models\ItemComment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
}
