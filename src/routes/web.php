<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ItemController::class, 'index'])->name('items.index'); // 商品一覧画面
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show')->middleware(['auth', 'verified', 'profile.completed']); // 商品詳細画面

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// メール認証関連
Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')->name('verification.notice'); // メール認証画面

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify'); // メール認証処理

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])->name('verification.send'); // メール認証通知送信

Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
    Route::post('/item/{item}/comments', [ItemController::class, 'storeComment'])->name('items.comments.store'); // 商品コメント投稿

    // プロフィール
    Route::get('/mypage', [ProfileController::class, 'show'])->name('profile.show'); // プロフィール画面
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // プロフィール編集画面
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update'); // プロフィール更新

    // 商品表示、出品
    Route::get('/sell', [ItemController::class, 'create'])->name('items.create'); // 商品出品画面
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store'); // 商品出品処理
    Route::post('/item/{item}/like', [ItemController::class, 'toggleLike'])->name('items.toggle_like'); // 商品いいね、いいね解除処理

    // 商品購入
    Route::get('/purchase/{item}', [PurchaseController::class, 'create'])->name('purchase.create'); // 商品購入画面
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->name('purchase.store'); // 商品購入処理

    // 住所変更
    Route::get('/purchase/address/{item}', [AddressController::class, 'edit'])->name('address.edit'); // 送付先住所編集画面
    Route::put('/purchase/address/{item}', [AddressController::class, 'update'])->name('address.update'); // 送付先住所更新処理
});
