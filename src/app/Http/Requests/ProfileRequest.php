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
            'profile_image' => ['image', 'mimes:jpeg,jpg,png'],
            'name' => ['required'],
            'zip_code' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address' => ['required'],
        ];
    }

    /**
     * バリデーションエラーメッセージのカスタマイズ
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'profile_image' => 'プロフィール画像',
            'name' => 'ユーザー名',
            'zip_code' => '郵便番号',
            'address' => '住所',
        ];
    }
}
