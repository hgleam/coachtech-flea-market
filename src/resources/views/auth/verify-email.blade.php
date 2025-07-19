@extends('layouts.app')

@section('content')
<div class='verify-page'>
    <div class='verify-page__body'>
        <div class='verify-page__message'>
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class='verify-page__message'>
                認証メールを再送しました。
            </div>
        @endif

        <div class='verify-form'>
            <form method='POST' action='{{ route("verification.send") }}' class='verify-form__group'>
                @csrf
                <button type='submit' class='verify-form__button'>
                    認証はこちらから
                </button>
            </form>

            @if (session('status') != 'verification-link-sent')
            <form method='POST' action='{{ route("verification.send") }}' class='verify-form__group'>
                @csrf
                <div class='verify-form__links'>
                    <a href='' class='verify-form__link'>
                        認証メールを再送する
                    </a>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection