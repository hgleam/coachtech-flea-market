<?php

namespace App\Http\Controllers;

use App\Enums\ItemCondition;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'all');
        $keyword = $request->input('keyword');

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $baseQuery = match ($tab) {
            'mylist' => Auth::check()
                ? $user->likedItems()
                : Item::whereRaw('false'), // 未ログイン時は空のクエリ
            default => Auth::check()
                ? Item::where('seller_id', '!=', Auth::id())
                : Item::query(),
        };

        // キーワードが存在すれば、商品名で部分一致検索
        $itemsQuery = $baseQuery->when($keyword, function ($query, $keyword) {
            return $query->where('name', 'like', '%' . $keyword . '%');
        });

        $items = $itemsQuery->with('order')->get();

        return view('items.index', compact('items', 'tab', 'keyword'));
    }

    /**
     * 商品詳細を表示
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $item = Item::with(['comments.user', 'categories', 'likedByUsers'])->findOrFail($id);
        $is_sold = ! is_null($item->buyer_id);

        return view('items.show', compact('item', 'is_sold'));
    }

    /**
     * 商品出品画面を表示します。
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $categories = Category::all();
        $conditions = ItemCondition::cases();

        return view('items.create', compact('categories', 'conditions'));
    }

    /**
     * 新しい商品を保存します。
     *
     * @param  \App\Http\Requests\ExhibitionRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExhibitionRequest $request)
    {
        $input = $request->only(['name', 'description', 'price', 'condition', 'brand_name', 'categories', 'image']);

        $imagePath = $request->file('image')->store('items', 'public');

        $item = Item::create([
            'seller_id' => Auth::id(),
            'name' => $input['name'],
            'description' => $input['description'],
            'price' => $input['price'],
            'condition' => $input['condition'],
            'brand_name' => $input['brand_name'],
            'image_path' => $imagePath,
        ]);

        $item->categories()->attach($input['categories']);

        return redirect()->route('items.index')->with('success', '商品を出品しました。');
    }

    /**
     * 商品コメントを投稿
     *
     * @param \App\Http\Requests\CommentRequest $request
     * @param \App\Models\Item $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeComment(CommentRequest $request, Item $item)
    {
        if (Auth::guest()) {
            return back();
        }

        $input = $request->only('comment');

        $item->comments()->create([
            'user_id' => auth()->id(),
            'item_id' => $item->id,
            'comment' => $input['comment'],
        ]);

        return back()->with('success', 'コメントを投稿しました');
    }

    /**
     * 商品のいいねを切り替えます。
     *
     * @param \App\Models\Item $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleLike(Item $item)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->likedItems()->toggle($item->id);

        return back();
    }
}
