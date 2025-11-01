<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * 取引メッセージファクトリー。
 */
class TradeMessageFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義します。
     *
     * @return array
     */
    public function definition()
    {
        return [
            'item_id' => Item::factory(),
            'user_id' => User::factory(),
            'message' => $this->faker->sentence,
            'image_path' => null,
        ];
    }
}
