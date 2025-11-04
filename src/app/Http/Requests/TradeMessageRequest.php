<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 取引メッセージリクエスト
 *
 * 取引メッセージのバリデーションを行います。
 */
class TradeMessageRequest extends FormRequest
{
    /**
     * 認証を許可します。
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルールを返します。
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => ['required', 'string', 'max:400'],
            'image' => ['nullable', 'mimetypes:image/jpeg,image/jpg,image/png', function ($attribute, $value, $fail) {
                if ($value) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    $allowedExtensions = ['jpeg', 'jpg', 'png'];
                    if (! in_array($extension, $allowedExtensions)) {
                        $fail('「.png」または「.jpeg」形式でアップロードしてください');
                    }
                }
            }],
        ];
    }

    /**
     * バリデーションエラーメッセージのカスタマイズを返します。
     *
     * @return array
     */
    public function messages()
    {
        return [
            'message.required' => '本文を入力してください',
            'message.max' => '本文は400文字以内で入力してください',
            'image.mimetypes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}
