<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse as VerifyEmailViewResponseContract;

/**
 * メール認証画面のレスポンス
 *
 * メール認証画面を表示します。
 */
class VerifyEmailViewResponse implements VerifyEmailViewResponseContract
{
    /**
     * メール認証画面を表示
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : view('auth.verify-email');
    }
}