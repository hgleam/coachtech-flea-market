<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\RegisteredUserController as FortifyRegisteredUserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

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
            FortifyRegisteredUserController::class,
            RegisteredUserController::class
        );

        $this->app->singleton(
            FortifyLoginRequest::class,
            LoginRequest::class
        );
    }

    /**
     * アプリケーションサービスをブート
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => [__('auth.failed')],
                ]);
            }

            return $user;
        });

        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Http\Responses\RegisterResponse::class
        );

        // Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        // Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        // Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // RateLimiter::for('login', function (Request $request) {
        //     $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

        //     return Limit::perMinute(5)->by($throttleKey);
        // });

        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });

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

            return Limit::perMinute(10)->by($email, $request->ip());
        });
    }
}
