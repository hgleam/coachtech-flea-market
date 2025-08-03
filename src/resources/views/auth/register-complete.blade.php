@extends('layouts.app')

@section('content')
<div class='register-complete-page'>
    <div class='register-complete-page__body'>
        <h2 class='register-complete__title'>会員登録完了</h2>
        <p class='register-complete__message'>
            会員登録が完了しました。<br>
            引き続きサービスをお楽しみください。
        </p>
        <a href="{{ route('home') }}" class="register-complete__button">
            トップページへ
        </a>
    </div>
</div>
@endsection