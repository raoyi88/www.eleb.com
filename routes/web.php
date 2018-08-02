<?php
Route::resource('client','ClientController');

Route::prefix('api')->group(function (){
    Route::get('shops','ShopController@index');
    Route::get('shops_info','ShopController@shopInfo');
    Route::post('regist','ShopController@regist');
    Route::get('sms','ShopController@sms');
    Route::post('loginCheck','ShopController@loginCheck');
    Route::post('addAddress','ShopController@addaddress');
    Route::get('addressList','ShopController@addressList');
    Route::get('address','ShopController@address');
    Route::post('editAddress','ShopController@editAddress');
    Route::post('addCart','ShopController@addCart');
    Route::post('addOrder','ShopController@addOrder');
    Route::get('orderList','shopController@orderList');
    Route::post('changePassword','shopController@changePassword');
    Route::get('cart','shopController@cart');
    Route::get('order','shopController@order');
//    Route::get('mail','shopController@mail');
});