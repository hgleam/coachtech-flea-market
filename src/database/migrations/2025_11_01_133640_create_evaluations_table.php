<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 評価マイグレーション。
 */
class CreateEvaluationsTable extends Migration
{
    /**
     * マイグレーションを実行します。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete(); // 評価する人
            $table->foreignId('evaluated_id')->constrained('users')->cascadeOnDelete(); // 評価される人
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete(); // 取引の商品
            $table->integer('rating'); // 評価値（1-5）
            $table->timestamps();

            // 同じ取引で同じ人を評価するのは1回のみ
            $table->unique(['evaluator_id', 'evaluated_id', 'item_id']);
        });
    }

    /**
     * マイグレーションを逆に実行します。
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluations');
    }
}
