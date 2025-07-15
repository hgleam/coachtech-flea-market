<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
            'zip_code' => '郵便番号',
            'address' => '住所',
        ];
    }
}
