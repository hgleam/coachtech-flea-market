<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AddressController extends Controller
{
    /**
     * 住所編集画面を表示
     */
    public function edit(Item $item)
    {
        $sessionKey = 'shipping_address_for_item_' . $item->id;
        $shippingAddress = session($sessionKey);
        $user = Auth::user();
        return view('address.edit', compact('user', 'item', 'shippingAddress'));
    }

    /**
     * 住所情報を更新
     */
    public function update(AddressRequest $request, Item $item)
    {
        $sessionKey = 'shipping_address_for_item_' . $item->id;
        session([$sessionKey => $request->validated()]);

        return redirect()->route('purchase.create', $item)->with('status', '配送先住所を設定しました。');
    }
}