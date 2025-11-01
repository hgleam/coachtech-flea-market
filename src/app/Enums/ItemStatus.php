<?php

namespace App\Enums;

/**
 * 商品の取引状態
 */
enum ItemStatus: string
{
    case SELLING = 'selling';
    case TRADING = 'trading';
    case COMPLETED = 'completed';
}

