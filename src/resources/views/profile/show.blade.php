@extends('layouts.app')

@section('content')
@php
    // GETパラメータ'page'を取得。なければ'sell'をデフォルトにする
    $page = request()->input('page', 'sell');
@endphp
<div class='profile-page'>
    <div class='profile-page__body'>
        <div class='profile-header'>
            <div class='profile-header__main'>
                <div class='profile-header__info'>
                    <div class='profile-header__avatar-name'>
                        <div class='profile-header__avatar'>
                            <img src='{{ $user->profile_image_path ? asset('storage/' . $user->profile_image_path) : 'https://placehold.co/200x200/e2e8f0/e2e8f0.png' }}'
                                 alt='プロフィール画像'
                                 class='profile-header__avatar-img'
                                 onerror="this.onerror=null; this.src='https://placehold.co/200x200/e2e8f0/e2e8f0.png';">
                        </div>
                        <h2 class='profile-header__name'>{{ $user->name }}</h2>
                    </div>
                    <a href='{{ route("profile.edit") }}' class='profile-header__edit-button'>プロフィールを編集</a>
                </div>
            </div>
        </div>

        <div class='profile-tabs'>
            <a href='{{ route("profile.show", ["page" => "sell"]) }}' class='profile-tabs__item {{ $page === "sell" ? "profile-tabs__item--active" : "" }}'>出品した商品</a>
            <a href='{{ route("profile.show", ["page" => "buy"]) }}' class='profile-tabs__item {{ $page === "buy" ? "profile-tabs__item--active" : "" }}'>購入した商品</a>
        </div>

        <div class='profile-items'>
            <div class='profile-items__grid'>
                @foreach ($items as $item)
                <a href='{{ route("items.show", $item->id) }}' class='item-card-profile'>
                    <div class='item-card-profile__image'>
                        <img src='{{ asset("storage/" . $item->image_path) }}'
                             alt='{{ $item->name }}'
                             class='item-card-profile__image-img'>
                    </div>
                    <h3 class='item-card-profile__title'>{{ $item->name }}</h3>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection