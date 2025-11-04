<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * プロフィールコントローラー
 *
 * プロフィール画面を表示し、プロフィール編集、プロフィール更新を行います。
 */
class ProfileController extends Controller
{
    /**
     * プロフィール画面を表示
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $page = $request->input('page', 'sell');

        $items = match ($page) {
            'buy' => $user->purchasedItems,
            'trading' => $user->getTradingItemsSortedByLatestMessage(),
            default => $user->soldItems,
        };

        return view('profile.show', compact('user', 'items', 'page'));
    }

    /**
     * プロフィール編集画面
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit()
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    /**
    * プロフィール更新処理
    *
    * @param ProfileRequest $request
    * @return \Illuminate\Http\RedirectResponse
    */
    public function update(ProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $validated = $request->only(['name', 'zip_code', 'address', 'building', 'profile_image']);

        if ($request->hasFile('profile_image')) {
            unset($validated['profile_image']);
            $path = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image_path = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'プロフィールを更新しました');
    }
}
