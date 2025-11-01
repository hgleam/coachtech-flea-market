<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 商品ステータスマイグレーション。
 */
class AddStatusToItemsTable extends Migration
{
    /**
     * マイグレーションを実行します。
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('status')->default(\App\Enums\ItemStatus::SELLING->value)->after('buyer_id'); // selling: 販売中, trading: 取引中, completed: 取引完了
        });
    }

    /**
     * マイグレーションを逆に実行します。
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
