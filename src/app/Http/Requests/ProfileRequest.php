<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * プロフィールリクエスト
 *
 * プロフィールリクエストを管理します。
 */
class ProfileRequest extends FormRequest
{
    /**
     * 認証
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array
     */
    public function rules()
    {
        return [
            'profile_image' => 'image|mimes:jpeg,png',
            'name' => 'required',
            'zip_code' => 'required|regex:/^\d{3}-\d{4}$/',
            'address' => 'required',
            'building' => 'required',
        ];
    }
}
