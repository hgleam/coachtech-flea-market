@extends('layouts.app')

@section('content')
<div class='create-page'>
    <div class='create-page__body'>
        <h2 class='create-page__title'>商品の出品</h2>

        <form method='POST' action='{{ route("items.store") }}' class='create-form' enctype='multipart/form-data'>
            @csrf

            <div class='create-form__section'>
                <h3 class='create-form__section-title'>商品画像</h3>
                <div class='create-form__image'>
                    <div class='create-form__image-preview'>
                        <label class='create-form__image-select' id="image-select-button">
                            画像を選択する
                        </label>
                        <input type='file' name="image" accept='image/*' id='image-input' style="display: none;">
                        <img src="" alt='商品画像' id='image-preview' class="hidden">
                    </div>
                    @error('image')
                        <p class='create-form__error'>{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class='create-form__section'>
                <h3 class='create-form__section-title'>商品の詳細</h3>

                <div class='create-form__categories'>
                    <p class='create-form__label'>カテゴリー</p>
                    <div class='create-form__category-grid'>
                        @foreach ($categories as $category)
                        <label class='create-form__category'>
                            <input type='checkbox' name='categories[]' value='{{ $category->id }}' {{ old('categories') && in_array($category->id, old('categories')) ? 'checked' : '' }}>
                            <span class='create-form__category-label'>{{ $category->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('categories')
                        <p class='create-form__error'>{{ $message }}</p>
                    @enderror
                </div>

                <div class='create-form__field'>
                    <label class='create-form__label'>商品の状態</label>
                    <select name='condition' class='create-form__select'>
                        <option value=''>選択してください</option>
                        @foreach ($conditions as $condition)
                            <option value='{{ $condition->value }}' {{ old('condition') == $condition->value ? 'selected' : '' }}>{{ $condition->value }}</option>
                        @endforeach
                    </select>
                    @error('condition')
                        <p class='create-form__error'>{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class='create-form__section'>
                <h3 class='create-form__section-title'>商品名と説明</h3>

                <div class='create-form__field'>
                    <label class='create-form__label'>商品名</label>
                    <input type='text' name='name' class='create-form__input' value='{{ old("name") }}'>
                    @error('name')
                        <p class='create-form__error'>{{ $message }}</p>
                    @enderror
                </div>

                <div class='create-form__field'>
                    <label class='create-form__label'>ブランド名</label>
                    <input type='text' name='brand_name' class='create-form__input' value='{{ old("brand") }}'>
                </div>

                <div class='create-form__field'>
                    <label class='create-form__label'>商品の説明</label>
                    <textarea name='description' class='create-form__textarea'>{{ old('description') }}</textarea>
                    @error('description')
                        <p class='create-form__error'>{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class='create-form__section'>
                <h3 class='create-form__section-title'>販売価格</h3>

                <div class='create-form__field'>
                    <input type='text' name='price' class='create-form__input' placeholder='¥' value='{{ old("price") }}'>
                    @error('price')
                        <p class='create-form__error'>{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type='submit' class='create-form__submit'>出品する</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .hidden { display: none; }
</style>
<script>
// 画像のプレビュー
document.addEventListener('DOMContentLoaded', function() {
    const imageSelectButton = document.getElementById('image-select-button');
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');

    // 画像のプレビューのボタンをクリックしたら画像のプレビューのボタンをクリックする
    imageSelectButton.addEventListener('click', () => imageInput.click());
    // 画像のプレビューの画像をクリックしたら画像のプレビューのボタンをクリックする
    imagePreview.addEventListener('click', () => imageInput.click());
    // 画像のプレビューの画像を選択したら画像のプレビューの画像を表示する
    imageInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        // 画像のプレビューの画像を表示する
        reader.onload = event => {
            imagePreview.src = event.target.result;
            imagePreview.classList.remove('hidden');
            imageSelectButton.classList.add('hidden');
        };
        // 画像のプレビューの画像を読み込む
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
