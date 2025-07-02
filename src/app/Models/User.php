<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
}
