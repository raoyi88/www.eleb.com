<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {


//            字段名称	类型	备注
//id	primary	主键
            $table->increments('id');
//user_id	int	用户ID
            $table->integer('user_id');
//shop_id	int	商家ID
            $table->integer('shop_id');
//sn	string	订单编号
            $table->string('sn');
//province	string	省
            $table->string('province');
//city	string	市
            $table->string('city');
//county	string	县
            $table->string('county');
//address	string	详细地址
            $table->string('address');
//tel	string	收货人电话
            $table->string('tel');
//name	string	收货人姓名
            $table->string('name');
//total	decimal	价格
            $table->decimal('total');
//status	int	状态(-1:已取消,0:待支付,1:待发货,2:待确认,3:完成)
            $table->integer('status');
//created_at	datetime	创建时间
            $table->string('created_time');
//            $table->dateTime('created_at');
//out_trade_no	string	第三方交易号（微信支付需要）
            $table->string('out_trade_no');
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
        Schema::dropIfExists('orders');
    }
}
