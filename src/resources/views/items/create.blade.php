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
                        <label class='create-form__image-select'>
                            画像を選択する
                            <input type='file' name='image' accept='image/*' class='create-form__image-input' id='imageInput'>
                        </label>
                        <img src='' alt='商品画像' id='imagePreview' style='display: none;'>
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
                        @foreach (App\Enums\ItemCondition::cases() as $condition)
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
                    <input type='text' name='brand' class='create-form__input' value='{{ old("brand") }}'>
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
                    <!-- <label class='create-form__label'>¥</label> -->
                    <input type='text' name='price' class='create-form__input' placeholder='¥' value='{{ old("price") }}'>
                    @error('price')
                        <p class='create-form__error'>{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type='submit' class='create-form__submit'>
                出品する
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('imageInput');
    const preview = document.getElementById('imagePreview');
    const previewContainer = document.querySelector('.create-form__image-preview');
    const selectButton = document.querySelector('.create-form__image-select');

    // 初期状態では画像プレビューを非表示
    preview.style.display = 'none';

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            // ファイルが画像かどうかチェック
            if (!file.type.startsWith('image/')) {
                alert('画像ファイルを選択してください');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                selectButton.style.opacity = '0';

                // ホバー時のイベントを設定
                const showButton = () => selectButton.style.opacity = '1';
                const hideButton = () => selectButton.style.opacity = '0';

                previewContainer.addEventListener('mouseenter', showButton);
                previewContainer.addEventListener('mouseleave', hideButton);
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            selectButton.style.opacity = '1';
        }
    });
});
</script>
@endpush

@endsection