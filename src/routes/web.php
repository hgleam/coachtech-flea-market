<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PurchaseController;

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

Route::get('/', [ItemController::class, 'index'])->name('items.index')->middleware('profile.completed'); // 商品一覧画面
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show')->middleware('profile.completed'); // 商品詳細画面

Route::middleware(['auth', 'profile.completed'])->group(function () {
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
