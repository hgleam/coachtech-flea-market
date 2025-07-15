@extends('layouts.app')

@section('title', '購入手続き')

@section('content')
<div class='purchase-page'>
    <form action='{{ route("purchase.store", $item) }}' method='POST' class='purchase-page__form'>
        @csrf
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
                    @error('payment_method')
                        <span class='purchase-page__error'>{{ $message }}</span>
                    @enderror
                </div>

                <div class='purchase-page__section'>
                    <div class='purchase-page__section-header'>
                        <h2 class='purchase-page__section-title'>配送先</h2>
                        <button type='button' id='address-change-button' data-url='{{ route("address.edit", $item) }}' class='purchase-page__address-change'>変更する</button>
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
                    <button type='submit' class='purchase-page__button'>購入する</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentSelect = document.getElementById('payment-method-select');
        const summaryPaymentMethod = document.getElementById('summary-payment-method');
        const storageKey = 'selectedPaymentMethod';

        function updatePaymentMethod() {
            if (paymentSelect.value) {
                const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
                summaryPaymentMethod.textContent = selectedOption.text;
            } else {
                summaryPaymentMethod.textContent = '';
            }
        }

        const savedMethod = sessionStorage.getItem(storageKey);
        if (savedMethod) {
            paymentSelect.value = savedMethod;
        }

        // 初期表示を更新
        updatePaymentMethod();

        paymentSelect.addEventListener('change', function() {
            updatePaymentMethod();
            sessionStorage.setItem(storageKey, paymentSelect.value);
        });

        // フォーム送信時にsessionStorageをクリアし、支払い方法をフォームに追加
        const purchaseForm = document.querySelector('.purchase-page__form');
        if (purchaseForm) {
            purchaseForm.addEventListener('submit', function() {
                sessionStorage.removeItem(storageKey);
            });
        }

        const addressChangeButton = document.getElementById('address-change-button');
        if (addressChangeButton) {
            addressChangeButton.addEventListener('click', function() {
                window.location.href = this.dataset.url;
            });
        }
    });
</script>
@endsection