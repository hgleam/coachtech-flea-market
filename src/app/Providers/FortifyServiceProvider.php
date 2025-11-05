<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest as AppLoginRequest;
use App\Http\Responses\VerifyEmailViewResponse as AppVerifyEmailViewResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

/**
 * Fortifyサービスプロバイダー
 *
 * Fortifyサービスプロバイダーを管理します。
 */
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * アプリケーションサービスを登録
     */
    public function register(): void
    {
        $this->app->singleton(
            VerifyEmailViewResponse::class,
            AppVerifyEmailViewResponse::class
        );

        // FortifyのLoginRequestをカスタムのLoginRequestにバインド
        $this->app->bind(
            FortifyLoginRequest::class,
            AppLoginRequest::class
        );
    }

    /**
     * アプリケーションサービスをブート
     */
    public function boot(): void
    {
        /**
         * 新規登録処理
         *
         * @param  \App\Http\Requests\RegisterRequest  $request
         * @param  \Laravel\Fortify\Contracts\CreatesNewUsers  $creator
         * @return \Illuminate\Http\RedirectResponse
         */
        Fortify::createUsersUsing(CreateNewUser::class);

        /**
         * 新規登録画面を表示
         *
         * @return \Illuminate\Contracts\View\View
         */
        Fortify::registerView(function () {
            return view('auth.register');
        });

        /**
         * ログイン画面を表示
         *
         * @return \Illuminate\Contracts\View\View
         */
        Fortify::loginView(function () {
            return view('auth.login');
        });

        /**
         * ログインリクエストを制限
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Cache\RateLimiting\Limit
         */
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . '|' . $request->ip());
        });
    }
}
