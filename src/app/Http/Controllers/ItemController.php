<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

/**
 * 商品コントローラー
 *
 * 商品一覧を管理します。
 */
class ItemController extends Controller
{
    /**
     * 商品一覧を表示
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $items = [];
        return view('items.index', compact('items'));
    }
}