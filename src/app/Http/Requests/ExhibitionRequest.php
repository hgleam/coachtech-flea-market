<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
     * バリデーションルールを定義する
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimetypes:image/jpeg,image/jpg,image/png'],
            'categories' => ['required', 'array'],
            'categories.*' => ['required', 'integer', 'exists:categories,id'],
            'condition' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
        ];
    }

    public function attributes()
    {
        return [
            'name' => '商品名',
            'description' => '商品の説明',
            'image' => '商品画像',
            'categories' => '商品のカテゴリー',
            'condition' => '商品の状態',
            'price' => '販売価格',
        ];
    }
}
