<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserItemLastReadTable extends Migration
{
    /**
     * マイグレーションを実行します。
     *
     * ユーザーごとに、各商品のチャット画面を最後に開いた時刻を記録します。
     * これにより、未読メッセージを判定できます。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_item_last_read', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            // ユーザーと商品の組み合わせは一意
            $table->unique(['user_id', 'item_id']);
        });
    }

    /**
     * マイグレーションを逆に実行します。
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_item_last_read');
    }
}
