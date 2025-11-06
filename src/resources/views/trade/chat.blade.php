@extends('layouts.app')

@section('title', '取引チャット')

@section('content')
@php
    // 共通変数の準備
    $tradePartner = $item->getTradePartner(Auth::user());
    $messages = $item->getOrderedTradeMessages();
    $otherTradingItems = Auth::user()->getTradingItemsExcept($item);
    $isSeller = $item->isSeller(Auth::user());
    $isCompleted = $item->isCompleted();

    // 評価関連の変数
    $evaluationInfo = $item->needsEvaluationBy(Auth::user());
    $needsEvaluation = $evaluationInfo['needs'];
    $evaluationTarget = $evaluationInfo['target'];
    // 評価が必要な場合（セッションまたはneedsEvaluationByの結果）にtrueを設定
    $needsEvaluationValue = (session('needs_evaluation') || ($needsEvaluation && $evaluationTarget)) ? 'true' : 'false';
@endphp

<div class='trade-chat-page'
     data-needs-evaluation='{{ $needsEvaluationValue }}'
     data-update-route-template='{{ route("trade.message.update", ["item" => $item, "message" => ":messageId"]) }}'
     data-csrf-token='{{ csrf_token() }}'>
    {{-- フラッシュメッセージ --}}
    @if (session('success'))
        <div class='trade-chat-page__alert trade-chat-page__alert--success'>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class='trade-chat-page__alert trade-chat-page__alert--danger'>
            {{ session('error') }}
        </div>
    @endif

    <div class='trade-chat-page__body'>
        {{-- サイドバー: その他の取引一覧 --}}
        <div class='trade-chat-page__sidebar'>
            <h2 class='trade-chat-page__sidebar-title'>その他の取引</h2>
            @if ($otherTradingItems->isEmpty())
                <p class='trade-chat-page__sidebar-empty'>他の取引はありません</p>
            @else
                <div class='trade-chat-page__sidebar-items'>
                    @foreach ($otherTradingItems as $otherItem)
                    <a href='{{ route("trade.chat", $otherItem->id) }}' class='trade-chat-page__sidebar-item'>
                        @if ($otherItem->hasUnreadMessages())
                        <div class='trade-chat-page__sidebar-item-notification-mark'>
                            <span class='trade-chat-page__sidebar-item-badge' aria-label='未読メッセージ {{ $otherItem->unread_count }}件' title='未読メッセージ {{ $otherItem->unread_count }}件'>
                                {{ $otherItem->unread_count }}
                            </span>
                        </div>
                        @endif
                        <span class='trade-chat-page__sidebar-item-name'>{{ $otherItem->name }}</span>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- メインコンテンツ --}}
        <div class='trade-chat-page__main'>
            {{-- ヘッダー: 取引相手情報と完了ボタン --}}
            <div class='trade-chat-page__header'>
                <div class='trade-chat-page__header-left'>
                    <div class='trade-chat-page__header-avatar'>
                        <img src='{{ $tradePartner->profile_image_path ? asset("storage/" . $tradePartner->profile_image_path) : asset("images/default-avatar.png") }}'
                            alt='{{ $tradePartner->name }}'>
                    </div>
                    <h1 class='trade-chat-page__header-title'>「{{ $tradePartner->name }}」さんとの取引画面</h1>
                </div>
                @if (!$isCompleted && !$isSeller)
                <form action='{{ route("trade.complete", $item) }}' method='POST' class='trade-chat-page__header-complete-form' novalidate>
                    @csrf
                    <button type='submit' class='trade-chat-page__header-complete-button' onclick="return confirm('取引を完了しますか？')">取引を完了する</button>
                </form>
                @endif
            </div>

            {{-- 商品情報 --}}
            <div class='trade-chat-page__item-section'>
                <div class='trade-chat-page__item-image-wrapper'>
                    <img src='{{ asset("storage/" . $item->image_path) }}' alt='{{ $item->name }}' class='trade-chat-page__item-image-main'>
                </div>
                <div class='trade-chat-page__item-info-section'>
                    <h2 class='trade-chat-page__item-name-main'>{{ $item->name }}</h2>
                    <p class='trade-chat-page__item-price-main'>¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            {{-- メッセージ一覧 --}}
            <div class='trade-chat-page__messages-area'>
                @if ($messages->isEmpty())
                    <div class='trade-chat-page__no-messages'>
                        <p>メッセージはまだありません</p>
                    </div>
                @else
                    @foreach ($messages as $message)
                    <div class='trade-chat-page__message {{ $message->user_id === Auth::id() ? "trade-chat-page__message--own" : "" }}'>
                        @if ($message->user_id !== Auth::id())
                            {{-- 相手のメッセージ --}}
                            <div class='trade-chat-page__message-wrapper'>
                                <div class='trade-chat-page__message-header-section'>
                                    <div class='trade-chat-page__message-avatar'>
                                        <img src='{{ $message->user->profile_image_path ? asset("storage/" . $message->user->profile_image_path) : asset("images/default-avatar.png") }}'
                                            alt='{{ $message->user->name }}'>
                                    </div>
                                    <span class='trade-chat-page__message-user-name'>{{ $message->user->name }}</span>
                                </div>
                                <p class='trade-chat-page__message-text'>{{ $message->message }}</p>
                                @if ($message->image_path)
                                <div class='trade-chat-page__message-image'>
                                    <img src='{{ asset("storage/" . $message->image_path) }}' alt='メッセージ画像' class='trade-chat-page__message-image-img' data-image-url='{{ asset("storage/" . $message->image_path) }}'>
                                </div>
                                @endif
                            </div>
                        @else
                            {{-- 自分のメッセージ --}}
                            <div class='trade-chat-page__message-content-wrapper'>
                                <div class='trade-chat-page__message-header-section trade-chat-page__message-header-section--own'>
                                    <span class='trade-chat-page__message-user-name trade-chat-page__message-user-name--own'>{{ $message->user->name }}</span>
                                    <div class='trade-chat-page__message-avatar trade-chat-page__message-avatar--own'>
                                        <img src='{{ $message->user->profile_image_path ? asset("storage/" . $message->user->profile_image_path) : asset("images/default-avatar.png") }}'
                                            alt='{{ $message->user->name }}'>
                                    </div>
                                </div>
                                <p class='trade-chat-page__message-text'>{{ $message->message }}</p>
                                @if ($message->image_path)
                                <div class='trade-chat-page__message-image'>
                                    <img src='{{ asset("storage/" . $message->image_path) }}' alt='メッセージ画像' class='trade-chat-page__message-image-img' data-image-url='{{ asset("storage/" . $message->image_path) }}'>
                                </div>
                                @endif
                                <div class='trade-chat-page__message-actions'>
                                    <button type='button' class='trade-chat-page__message-action trade-chat-page__message-action--edit' data-message-id='{{ $message->id }}' data-message-text='{{ $message->message }}'>編集</button>
                                    <form action='{{ route("trade.message.destroy", ["item" => $item, "message" => $message]) }}' method='POST' class='trade-chat-page__message-action-form' style='display: inline;'>
                                        @csrf
                                        @method('DELETE')
                                        <button type='submit' class='trade-chat-page__message-action trade-chat-page__message-action--delete' onclick="return confirm('メッセージを削除しますか？')">削除</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- メッセージ入力フォーム --}}
            @if (!$isCompleted)
                <div class='trade-chat-page__input-area'>
                    <form action='{{ route("trade.message.store", $item) }}' method='POST' class='trade-chat-page__message-form' enctype='multipart/form-data' novalidate>
                        @csrf
                        @error('message')
                            <p class='trade-chat-page__error'>{{ $message }}</p>
                        @enderror
                        @error('image')
                            <p class='trade-chat-page__error'>{{ $message }}</p>
                        @enderror
                        <div class='trade-chat-page__input-wrapper'>
                            <textarea name='message' id='message-input' class='trade-chat-page__message-input' placeholder='取引メッセージを記入してください' rows='1' required data-item-id='{{ $item->id }}' data-user-id='{{ Auth::id() }}'>{{ old('message', session('message_input_' . Auth::id() . '_' . $item->id, '')) }}</textarea>
                            <div class='trade-chat-page__input-buttons'>
                                <label for='image-input' class='trade-chat-page__image-button'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                        <rect x='3' y='3' width='18' height='18' rx='2' ry='2'/>
                                        <circle cx='8.5' cy='8.5' r='1.5'/>
                                        <polyline points='21 15 16 10 5 21'/>
                                    </svg>
                                    <span>画像を追加</span>
                                </label>
                                <input type='file' id='image-input' name='image' accept='image/jpeg,image/png' style='display: none;'>
                                <button type='submit' class='trade-chat-page__send-button-icon'>
                                    <img src='{{ asset("images/message-send.jpg") }}' alt='送信' class='trade-chat-page__send-button-icon-img'>
                                </button>
                            </div>
                        </div>
                        <div id='image-preview-container' class='trade-chat-page__image-preview-container' style='display: none;'>
                            <div class='trade-chat-page__image-preview-wrapper'>
                                <img id='image-preview' src='' alt='プレビュー画像' class='trade-chat-page__image-preview-img'>
                                <button type='button' id='image-remove-button' class='trade-chat-page__image-remove-button' aria-label='画像を削除'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                        <line x1='18' y1='6' x2='6' y2='18'/>
                                        <line x1='6' y1='6' x2='18' y2='18'/>
                                    </svg>
                                </button>
                            </div>
                            <p class='trade-chat-page__image-preview-filename' id='image-preview-filename'></p>
                        </div>
                    </form>
                </div>
            @elseif ($isCompleted && $item->isFullyEvaluated())
                <div class='trade-chat-page__completed'>
                    <p class='trade-chat-page__completed-text'>この取引は完了しました</p>
                </div>
            @endif
        </div>
    </div>

    {{-- 評価モーダル --}}
    @if ($needsEvaluation && $evaluationTarget)
    <div class='trade-chat-page__evaluation-modal' id='evaluationModal'>
        <div class='trade-chat-page__evaluation-modal-overlay'></div>
        <div class='trade-chat-page__evaluation-modal-content'>
            <h2 class='trade-chat-page__evaluation-modal-title'>取引が完了しました。</h2>
            <p class='trade-chat-page__evaluation-modal-subtitle'>今回の取引相手はどうでしたか?</p>
            <form action='{{ route("trade.evaluation.store", $item) }}' method='POST' class='trade-chat-page__evaluation-form'>
                @csrf
                <div class='trade-chat-page__evaluation-rating'>
                    <div class='trade-chat-page__evaluation-stars'>
                        <input type='radio' name='rating' value='5' id='rating5' required>
                        <label for='rating5'>★</label>
                        <input type='radio' name='rating' value='4' id='rating4' required>
                        <label for='rating4'>★</label>
                        <input type='radio' name='rating' value='3' id='rating3' required>
                        <label for='rating3'>★</label>
                        <input type='radio' name='rating' value='2' id='rating2' required>
                        <label for='rating2'>★</label>
                        <input type='radio' name='rating' value='1' id='rating1' required>
                        <label for='rating1'>★</label>
                    </div>
                </div>
                <div class='trade-chat-page__evaluation-actions'>
                    <button type='submit' class='trade-chat-page__evaluation-submit'>
                        <img src='{{ asset("images/message-send.jpg") }}' alt='送信' class='trade-chat-page__evaluation-submit-icon'>
                        <span>送信する</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- 画像拡大表示モーダル --}}
    <div id='imageModal' class='trade-chat-page__image-modal' onclick='closeImageModal()'>
        <div class='trade-chat-page__image-modal-content' onclick='event.stopPropagation()'>
            <button type='button' class='trade-chat-page__image-modal-close' onclick='closeImageModal()' aria-label='閉じる'>
                ×
            </button>
            <img id='imageModalImg' src='' alt='拡大画像' class='trade-chat-page__image-modal-img'>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='{{ asset("js/trade-chat.js") }}'></script>
@endpush
