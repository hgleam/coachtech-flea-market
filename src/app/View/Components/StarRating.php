<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class StarRating extends Component
{
    /**
     * 評価値（整数、四捨五入済み）
     *
     * @var int|null
     */
    public ?int $rating;

    /**
     * 最大星数
     *
     * @var int
     */
    public int $maxStars;

    /**
     * 完全に塗りつぶされた星の数
     *
     * @var int
     */
    public int $fullStarsCount;

    /**
     * 半分星を表示するかどうか
     *
     * @var bool
     */
    public bool $shouldShowHalfStar;

    /**
     * コンポーネントのインスタンスを作成します。
     *
     * @param int|null $rating 評価値（nullの場合は評価なし、整数で四捨五入済み）
     * @param int $maxStars 最大星数（デフォルト: 5）
     */
    public function __construct(?int $rating = null, int $maxStars = 5)
    {
        $this->rating = $rating;
        $this->maxStars = $maxStars;

        if ($rating !== null) {
            // 整数に四捨五入済みなので、完全な星のみ表示
            $this->fullStarsCount = min($rating, $maxStars);
            $this->shouldShowHalfStar = false;
        } else {
            $this->fullStarsCount = 0;
            $this->shouldShowHalfStar = false;
        }
    }

    /**
     * コンポーネントのビューを取得します。
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.star-rating');
    }
}
