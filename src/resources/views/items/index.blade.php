@extends('layouts.app')

@section('content')
<div class='profile-page'>
    <div class='profile-page__body'>
        <div class='profile-tabs'>
            <a href='{{ route("items.index", ["keyword" => $keyword]) }}' class='profile-tabs__item {{ $tab !== "mylist" ? "profile-tabs__item--active" : "" }}'>おすすめ</a>
            <a href='{{ route("items.index", ["tab" => "mylist", "keyword" => $keyword]) }}' class='profile-tabs__item {{ $tab === "mylist" ? "profile-tabs__item--active" : "" }}'>マイリスト</a>
        </div>

        <div class='profile-items'>
            <div class='profile-items__grid'>
                @foreach ($items as $item)
                <a href='{{ route("items.show", $item->id) }}' class='item-card-profile'>
                    <div class='item-card-profile__image'>
                        @auth
                            @if ($item->order && $item->order->buyer_id == Auth::id())
                                <div class='item-card-profile__sold-label'>Sold</div>
                            @endif
                        @endauth
                        <img src='{{ asset("storage/" . $item->image_path) }}' alt='{{ $item->name }}' class='item-card-profile__image-img'>
                    </div>
                    <h3 class='item-card-profile__title'>{{ $item->name }}</h3>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection