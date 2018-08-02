<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_goods', function (Blueprint $table) {
            $table->increments('id');
//            order_id	int	订单id
            $table->integer('order_id');
//goods_id	int	商品id
            $table->integer('goods_id');
//amount	int	商品数量
            $table->integer('amount');
//goods_name	string	商品名称
            $table->string('goods_name');
//goods_img	string	商品图片
            $table->string('goods_img');
//goods_price	decimal	商品价格
            $table->decimal('goods_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_goods');
    }
}
