<?php

namespace App\Enums;

/**
 * 商品の状態
 */
enum ItemCondition: string
{
    case MINT = '良好';
    case VERY_GOOD = '目立った傷や汚れなし';
    case GOOD = 'やや傷や汚れあり';
    case POOR = '状態が悪い';
}
