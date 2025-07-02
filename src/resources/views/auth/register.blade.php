@extends('layouts.app')

@section('content')
<div class='register-page'>
    <div class='register-page__body'>
        <h1 class='register-page__title'>会員登録</h1>

        <form method='POST' action='{{ route("register") }}' class='register-form' novalidate>
            @csrf

            <div class='register-form__group'>
                <label for='username' class='register-form__label'>ユーザー名</label>
                <input id='username' type='text' class='register-form__input @error("username") register-form__input--error @enderror' name='username' value='{{ old("username") }}' required autocomplete='username' autofocus>
                @error('username')
                    <span class='register-form__error' role='alert'>{{ $message }}</span>
                @enderror
            </div>

            <div class='register-form__group'>
                <label for='email' class='register-form__label'>メールアドレス</label>
                <input id='email' type='email' class='register-form__input @error("email") register-form__input--error @enderror' name='email' value='{{ old("email") }}' required autocomplete='email'>
                @error('email')
                    <span class='register-form__error' role='alert'>{{ $message }}</span>
                @enderror
            </div>

            <div class='register-form__group'>
                <label for='password' class='register-form__label'>パスワード</label>
                <input id='password' type='password' class='register-form__input @error("password") register-form__input--error @enderror' name='password' required autocomplete='new-password'>
                @error('password')
                    <span class='register-form__error' role='alert'>{{ $message }}</span>
                @enderror
            </div>

            <div class='register-form__group'>
                <label for='password-confirm' class='register-form__label'>確認用パスワード</label>
                <input id='password-confirm' type='password' class='register-form__input @error('password_confirmation') register-form__input--error @enderror' name='password_confirmation' required autocomplete='new-password'>
                @error('password_confirmation')
                    <span class='register-form__error' role='alert'>{{ $message }}</span>
                @enderror
            </div>

            <div class='register-form__actions'>
                <button type='submit' class='register-form__button'>
                    登録する
                </button>
            </div>

            <div class='register-form__links'>
                <a href='{{ route("login") }}' class='register-form__link'>
                    ログインはこちら
                </a>
            </div>
        </form>
    </div>
</div>
@endsection