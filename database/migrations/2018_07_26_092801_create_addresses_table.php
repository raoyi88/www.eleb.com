<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
//user_id	int	用户id
            $table->string('province');
//province	string	省
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
//is_default	int	是否是默认地址
            $table->integer('is_default');
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
        Schema::dropIfExists('addresses');
    }
}
