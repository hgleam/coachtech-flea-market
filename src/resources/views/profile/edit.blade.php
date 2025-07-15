@extends('layouts.app')

@section('content')
<div class='profile-page'>
    <div class='profile-page__body'>
        <h2 class='profile-edit__title'>プロフィール設定</h2>

        <form method='POST' action='{{ route("profile.update") }}' class='profile-edit__form' enctype='multipart/form-data' novalidate>
            @csrf
            @method('PUT')

            <div class='profile-edit__image'>
                <div class='profile-edit__image-preview'>
                    <img id='profileImagePreview'
                         src='{{ old("profile_image", $user->profile_image_path) ? asset("storage/" . $user->profile_image_path) : asset("images/default-avatar.png") }}'
                         alt='プロフィール画像'>
                </div>
                <div class='profile-edit__image-select'>
                    <label class='profile-edit__image-button'>
                        画像を選択する
                        <input id='profileImageInput' type='file' name='profile_image' accept='image/*' class='profile-edit__image-input'>
                    </label>
                </div>
                @error('profile_image')
                <span class='profile-edit__error'>{{ $message }}</span>
            @enderror
            </div>

            <div class='profile-edit__field'>
                <label class='profile-edit__label'>ユーザー名</label>
                <input type='text' name='name' value='{{ old("name", $user->name) }}' class='profile-edit__input' required>
                @error('name')
                <span class='profile-edit__error'>{{ $message }}</span>
                @enderror
            </div>

            <div class='profile-edit__field'>
                <label class='profile-edit__label'>郵便番号</label>
                <input type='text' name='zip_code' value='{{ old("zip_code", $user->zip_code) }}' class='profile-edit__input' required>
                @error('zip_code')
                <span class='profile-edit__error'>{{ $message }}</span>
                @enderror
            </div>

            <div class='profile-edit__field'>
                <label class='profile-edit__label'>住所</label>
                <input type='text' name='address' value='{{ old("address", $user->address) }}' class='profile-edit__input' required>
                @error('address')
                <span class='profile-edit__error''>{{ $message }}</span>
                @enderror
            </div>

            <div class='profile-edit__field'>
                <label class='profile-edit__label'>建物名</label>
                <input type='text' name='building' value='{{ old("building", $user->building) }}' class='profile-edit__input'>
            </div>

            <button type='submit' class='profile-edit__submit'>
                更新する
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
// 画像のプレビュー
document.addEventListener('DOMContentLoaded', function() {
    // 画像のプレビューのボタン
    const imageInput = document.getElementById('profileImageInput');
    // 画像のプレビューの画像
    const imagePreview = document.getElementById('profileImagePreview');
    // 画像のプレビューの画像のデフォルト画像
    const defaultAvatarUrl = "{{ asset('images/default-avatar.png') }}";

    // 画像のプレビューの画像のデフォルト画像を表示する
    imagePreview.addEventListener('error', function() {
        this.src = defaultAvatarUrl;
    });

    // 画像のプレビューの画像を選択したら画像のプレビューの画像を表示する
    imageInput.addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            const reader = new FileReader();
            // 画像のプレビューの画像を表示する
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            };
            // 画像のプレビューの画像を読み込む
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush
@endsection