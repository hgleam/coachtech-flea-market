<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 無限リダイレクトを防ぐため、プロフィール関連のルートはチェック対象外にする
        $isProfileRoute = $request->routeIs('profile.edit') || $request->routeIs('profile.update');

        // ログインしていて、かつ現在のルートがプロフィール関連ではない場合
        if (Auth::check() && !$isProfileRoute) {
            $profileIsIncomplete = empty($user->name) ||
                                   empty($user->zip_code) ||
                                   empty($user->address) ||
                                   empty($user->building);

            if ($profileIsIncomplete) {
                return redirect()->route('profile.edit')->with('warning', 'プロフィール情報をすべて入力してください。');
            }
        }

        return $next($request);
    }
}
