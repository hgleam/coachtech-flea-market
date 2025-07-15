@extends('layouts.app')

@section('title', $item->name)

@section('content')
<div class='item-page'>
    <div class='item-page__body'>
        <div class='item-page__image-container'>
            <div class='item-page__image'>
                <div style='position: absolute; top: 0; left: 0; right: 0; bottom: 0; padding: 24px;'>
                    <img src='{{ asset("storage/" . $item->image_path) }}' alt='{{ $item->name }}' style='width: 100%; height: 100%; object-fit: contain;'>
                </div>
            </div>
        </div>

        <div class='item-page__info-container'>
            <h1 class='item-page__title'>{{ $item->name }}</h1>
            <p class='item-page__brand'>{{ $item->brand }}</p>

            <div class='item-page__purchase-info'>
                <p class='item-page__price'>
                    ¥{{ number_format($item->price) }}
                    <span class='item-page__tax'>(税込)</span>
                </p>

                <div class='item-page__stats'>
                    @auth
                    <form method='POST' action='{{ route("items.toggle_like", $item) }}' class='item-page__stat item-page__stat--like'>
                        @csrf
                        <button type='submit' style='background: none; border: none; padding: 0; cursor: pointer; display: flex; flex-direction: column; align-items: center; row-gap: 4px;'>
                            <div class='item-page__stat-icon'>
                                <svg xmlns='http://www.w3.org/2000/svg' height='24px' viewBox='0 0 24 24' width='24px' fill='{{ Auth::check() && $item->likedByUsers->contains(Auth::user()) ? '#ff2d55' : '#cccccc' }}'>
                                    <path d='M0 0h24v24H0V0z' fill='none'/>
                                    <path d='M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27z'/>
                                </svg>
                            </div>
                            <div class='item-page__stat-count'>{{ $item->likedByUsers->count() }}</div>
                        </button>
                    </form>
                    @else
                    <div class='item-page__stat item-page__stat--like'>
                        <span class='item-page__stat-icon'>
                            <svg xmlns='http://www.w3.org/2000/svg' height='24px' viewBox='0 0 24 24' width='24px' fill='#cccccc'>
                                <path d='M0 0h24v24H0V0z' fill='none'/>
                                <path d='M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27z'/>
                            </svg>
                        </span>
                        <span class='item-page__stat-count'>{{ $item->likedByUsers->count() }}</span>
                    </div>
                    @endauth
                    <div class='item-page__stat'>
                        <span class='item-page__stat-icon'>
                            <svg xmlns='http://www.w3.org/2000/svg' height='24px' viewBox='0 0 24 24' width='24px' fill='currentColor'><path d='M0 0h24v24H0V0z' fill='none'/><path d='M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z'/></svg>
                        </span>
                        <span class='item-page__stat-count'>{{ $item->comments->count() }}</span>
                    </div>
                </div>
            </div>

            <a href='{{ route("purchase.create", $item) }}' class='item-page__button'>購入手続きへ</a>

            <div class='item-page__section'>
                <h2 class='item-page__section-title'>商品説明</h2>
                <div class='item-page__description'>{{ $item->description }}</div>
            </div>

            <div class='item-page__info'>
                <h2 class='item-page__section-title'>商品の情報</h2>
                <div class='item-page__info-row'>
                    <span class='item-page__info-label'>カテゴリー</span>
                    <div class='item-page__categories'>
                        @foreach ($item->categories as $category)
                        <span class='item-page__category'>{{ $category->name }}</span>
                        @endforeach
                    </div>
                </div>
                <div class='item-page__info-row'>
                    <span class='item-page__info-label'>商品の状態</span>
                    <span class='item-page__info-value'>{{ $item->condition->value }}</span>
                </div>
            </div>

            <div class='item-page__comments'>
                <h2 class='item-page__comments-title'>
                    コメント
                    <span class='item-page__comments-count'>({{ $item->comments->count() }})</span>
                </h2>

                @foreach ($item->comments as $comment)
                <div class='item-page__comment'>
                    <div class='item-page__comment-header'>
                        <div class='item-page__comment-avatar'>
                            @if ($comment->user->profile_image_path)
                            <img src='{{ asset("storage/" . $comment->user->profile_image_path) }}' alt='{{ $comment->user->name }}'>
                            @endif
                        </div>
                        <span class='item-page__comment-user-name'>{{ $comment->user->name }}</span>
                    </div>
                    <p class='item-page__comment-text'>{{ $comment->comment }}</p>
                </div>
                @endforeach

                <div class='item-page__comment-form'>
                    <h3 class='item-page__comment-form-title'>商品へのコメント</h3>
                    <form action='{{ route("items.comments.store", $item) }}' method='POST'>
                        @csrf
                        <textarea class='item-page__comment-input' name='comment' value='{{ old("comment") }}' placeholder='コメントを入力してください'></textarea>
                        @error('comment')
                        <p class='item-page__comment-error '>{{ $message }}</p>
                        @enderror
                        <button type='submit' class='item-page__comment-button'>コメントを送信する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection