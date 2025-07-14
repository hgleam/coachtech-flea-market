<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Enums\ItemCondition;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'seller_id' => 1,
                'condition' => ItemCondition::MINT,
                'name' => '腕時計',
                'brand_name' => '',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000,
                'image_path' => 'images/items/1.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::VERY_GOOD,
                'name' => 'HDD',
                'brand_name' => '',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000,
                'image_path' => 'images/items/2.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::GOOD,
                'name' => '玉ねぎ3束',
                'brand_name' => '',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300,
                'image_path' => 'images/items/3.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::POOR,
                'name' => '革靴',
                'brand_name' => '',
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000,
                'image_path' => 'images/items/4.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::MINT,
                'name' => 'ノートPC',
                'brand_name' => '',
                'description' => '高性能なノートパソコン',
                'price' => 45000,
                'image_path' => 'images/items/5.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::VERY_GOOD,
                'name' => 'マイク',
                'brand_name' => '',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000,
                'image_path' => 'images/items/6.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::GOOD,
                'name' => 'ショルダーバッグ',
                'brand_name' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500,
                'image_path' => 'images/items/7.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::POOR,
                'name' => 'タンブラー',
                'brand_name' => '',
                'description' => '使いやすいタンブラー',
                'price' => 500,
                'image_path' => 'images/items/8.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::MINT,
                'name' => 'コーヒーミル',
                'brand_name' => '',
                'description' => '手動のコーヒーミル',
                'price' => 4000,
                'image_path' => 'images/items/9.jpg',
            ],
            [
                'seller_id' => 1,
                'condition' => ItemCondition::VERY_GOOD,
                'name' => 'メイクセット',
                'brand_name' => '',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'image_path' => 'images/items/10.jpg',
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
