<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * 評価ファクトリー。
 */
class EvaluationFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義します。
     *
     * @return array
     */
    public function definition()
    {
        return [
            'evaluator_id' => User::factory(),
            'evaluated_id' => User::factory(),
            'item_id' => Item::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
        ];
    }
}
