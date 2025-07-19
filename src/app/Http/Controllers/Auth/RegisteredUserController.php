<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * 新規登録コントローラー
 *
 * 新規登録画面を表示し、新規登録処理を行います。
 */
class RegisteredUserController extends Controller
{
    /**
     * 新規登録画面を表示
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * 新規登録処理
     *
     * @param  \App\Http\Requests\RegisterRequest  $request
     * @param  \Laravel\Fortify\Contracts\CreatesNewUsers  $creator
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RegisterRequest $request, CreatesNewUsers $creator): RedirectResponse
    {
        $user = $request->only(['username', 'email', 'password', 'password_confirmation']);

        event(new Registered($user = $creator->create($user)));

        Auth::login($user);

        return redirect()->intended(config('fortify.home'));
    }
}