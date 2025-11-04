<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Models\Item;
use App\Http\Requests\PurchaseRequest;
use App\Models\Order;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * 商品購入ページを表示します。
     *
     * @param \App\Models\Item $item
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Item $item)
    {
        if (!is_null($item->buyer_id)) {
            return redirect()->route('items.show', $item)->with('error', 'この商品は既に購入されています');
        }

        $sessionKey = 'shipping_address_for_item_' . $item->id;
        $shippingAddress = session($sessionKey);

        return view('items.purchase', compact('item', 'shippingAddress'));
    }

    /**
     * 商品の購入処理を行います。
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Item $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PurchaseRequest $request, Item $item)
    {
        if (!is_null($item->buyer_id)) {
            return redirect()->route('items.show', $item)->with('error', 'この商品は既に購入されています');
        }
        try {
            DB::transaction(function () use ($request, $item) {
                // 購入商品を作成
                $order = Order::create([
                    'item_id' => $item->id,
                    'buyer_id' => Auth::id(),
                    'payment_method' => $request->payment_method,
                ]);

                // 商品に購入者IDをセットし、取引状態を「取引中」に設定
                $item->update([
                    'buyer_id' => Auth::id(),
                    'status' => ItemStatus::TRADING->value,
                ]);

                // 配送先住所を保存
                $sessionKey = 'shipping_address_for_item_' . $item->id;
                $addressData = session($sessionKey);

                if (!$addressData) {
                    $user = Auth::user();
                    $addressData = [
                        'zip_code' => $user->zip_code,
                        'address' => $user->address,
                        'building' => $user->building,
                    ];
                }

                ShippingAddress::create([
                    'order_id' => $order->id,
                    'zip_code' => $addressData['zip_code'],
                    'address' => $addressData['address'],
                    'building' => $addressData['building'],
                ]);

                // セッションから配送先住所情報を削除
                if (session()->has($sessionKey)) {
                    session()->forget($sessionKey);
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '購入処理中にエラーが発生しました。もう一度お試しください。');
        }

        return redirect()->route('items.index')->with('success', '商品を購入しました。');
    }
}