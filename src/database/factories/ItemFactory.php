<?php

namespace Database\Factories;

use App\Enums\ItemCondition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * 商品データを作成
     *
     * @return array
     */
    public function definition()
    {
        return [
            'seller_id' => User::factory(),
            'condition' => $this->faker->randomElement(ItemCondition::cases()),
            'brand_name' => $this->faker->company,
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->numberBetween(100, 10000),
            'image_path' => 'images/item_default.png',
        ];
    }
}