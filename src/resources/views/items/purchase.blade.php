@extends('layouts.app')

@section('title', '購入手続き')

@section('content')
<div class='purchase-page'>
    <div class='purchase-page__body'>
        <div class='purchase-page__main'>
            <div class='purchase-page__item'>
                <div class='purchase-page__item-image'>
                    @if ($item->image_path)
                        <img src='{{ asset("storage/" . $item->image_path) }}' alt='{{ $item->name }}'>
                    @endif
                </div>
                <div class='purchase-page__item-info'>
                    <h1 class='purchase-page__item-name'>{{ $item->name }}</h1>
                    <p class='purchase-page__item-price'>¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            <div class='purchase-page__section'>
                <div class='purchase-page__section-header'>
                    <h2 class='purchase-page__section-title'>支払い方法</h2>
                </div>
                <div class='purchase-page__select-wrapper'>
                    <select name='payment_method' class='purchase-page__select' id='payment-method-select'>
                        <option value='' disabled selected>選択してください</option>
                        <option value='convenience_store'>コンビニ払い</option>
                        <option value='credit_card'>クレジットカード</option>
                        <option value='bank_transfer'>銀行振込</option>
                    </select>
                </div>
            </div>

            <div class='purchase-page__section'>
                <div class='purchase-page__section-header'>
                    <h2 class='purchase-page__section-title'>配送先</h2>
                    <a href='{{ route("address.edit", $item) }}' class='purchase-page__address-change'>変更する</a>
                </div>
                <div class='purchase-page__address-wrapper'>
                    <div class='purchase-page__address'>
                        <p>〒　{{ $shippingAddress['zip_code'] ?? Auth::user()->zip_code }}</p>
                        <p>{{ $shippingAddress['address'] ?? Auth::user()->address }}{{ $shippingAddress['building'] ?? Auth::user()->building }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class='purchase-page__side'>
            <div class='purchase-page__summary'>
                <div class='purchase-page__row'>
                    <span>商品代金</span>
                    <span>¥{{ number_format($item->price) }}</span>
                </div>
                <div class='purchase-page__row'>
                    <span>支払い方法</span>
                    <span id='summary-payment-method'></span>
                </div>
                <form action='{{ route("purchase.store", $item) }}' method='POST' class='purchase-page__form'>
                    @csrf
                    <button type='submit' class='purchase-page__button'>購入する</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentSelect = document.getElementById('payment-method-select');
        const summaryPaymentMethod = document.getElementById('summary-payment-method');

        function updatePaymentMethod() {
            const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
            if (paymentSelect.selectedIndex !== 0) {
                summaryPaymentMethod.textContent = selectedOption.text;
            } else {
                summaryPaymentMethod.textContent = '';
            }
        }

        // 初期表示
        updatePaymentMethod();

        // 変更時に更新
        paymentSelect.addEventListener('change', updatePaymentMethod);
    });
</script>
@endsection