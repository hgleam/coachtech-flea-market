@if ($rating !== null)
<div class='profile-header__rating'>
    @for ($starIndex = 1; $starIndex <= $maxStars; $starIndex++)
        @if ($starIndex <= $fullStarsCount)
            {{-- 完全に塗りつぶされた星 --}}
            <span class='profile-header__rating-star profile-header__rating-star--filled'>★</span>
        @elseif ($starIndex === $fullStarsCount + 1 && $shouldShowHalfStar)
            {{-- 半分塗りつぶされた星 --}}
            <span class='profile-header__rating-star profile-header__rating-star--half'>
                <span class='profile-header__rating-star-empty'>★</span>
                <span class='profile-header__rating-star-filled'>★</span>
            </span>
        @else
            {{-- グレーの星（評価なし） --}}
            <span class='profile-header__rating-star'>★</span>
        @endif
    @endfor
</div>
@endif

