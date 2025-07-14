@extends('layouts.app')

@section('title', '住所の変更')

@section('content')
<div class='address-page'>
    <div class='address-page__body'>
        <h2 class='address-page__title'>住所の変更</h2>

        <form method='POST' action='{{ route("address.update", $item) }}' class='address-page__form'>
            @csrf
            <div class='address-page__field'>
                <label for='zip_code' class='address-page__label'>郵便番号</label>
                <input type='text' name='zip_code' id='zip_code' class='address-page__input' value='{{ old("zip_code", $shippingAddress["zip_code"] ?? $user->zip_code) }}' required>
                @error('zip_code')
                    <p class='address-page__error'>{{ $message }}</p>
                @enderror
            </div>

            <div class='address-page__field'>
                <label for='address' class='address-page__label'>住所</label>
                <input type='text' name='address' id='address' class='address-page__input' value='{{ old("address", $shippingAddress["address"] ?? $user->address) }}' required>
                @error('address')
                    <p class='address-page__error'>{{ $message }}</p>
                @enderror
            </div>

            <div class='address-page__field'>
                <label for='building' class='address-page__label'>建物名</label>
                <input type='text' name='building' id='building' class='address-page__input' value='{{ old("building", $shippingAddress["building"] ?? $user->building) }}'>
                @error('building')
                    <p class='address-page__error'>{{ $message }}</p>
                @enderror
            </div>

            <button type='submit' class='address-page__button'>
                更新する
            </button>
        </form>
    </div>
</div>
@endsection