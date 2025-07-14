<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ユーザーモデル。
 *
 * アプリケーションのユーザー情報を管理します。
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * マスアサインメントで一括代入を許可する属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image_path',
        'zip_code',
        'address',
        'building',
    ];

    /**
     * 配列やJSONに含めない（隠す）属性。
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * ネイティブな型にキャストする属性。
     *
     * @var array<string, string>
     */
    protected $casts = [
    ];

    /**
     * ユーザーが出品した商品を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function soldItems(): HasMany
    {
        return $this->hasMany(Item::class, 'seller_id');
    }

    /**
     * ユーザーが購入した注文を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchasedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * ユーザーが購入した商品を中間テーブル(orders)経由で取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function purchasedItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            Item::class,
            Order::class,
            'buyer_id', // ordersテーブルの外部キー
            'id',       // itemsテーブルの外部キー
            'id',       // usersテーブルのローカルキー
            'item_id'   // ordersテーブルのローカルキー
        );
    }

    /**
     * ユーザーがいいねした商品を取得します。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedItems(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'liked_items');
    }
}
