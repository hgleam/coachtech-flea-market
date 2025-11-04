<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

/**
 * 新規登録レスポンス
 *
 * 新規登録レスポンスを管理します。
 */
class RegisterResponse implements RegisterResponseContract
{
    /**
     * レスポンスを作成
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->route('login')->with('status', '会員登録が完了しました。ログインしてください。');
    }
}
