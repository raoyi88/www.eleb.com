<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Member;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\Shop;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ShopController extends Controller
{
    //商家列表接口
    public function index()
    {
        $shops = Shop::all();
        return response()->json($shops);
    }
    //指定商家接口
    public function shopInfo(Request $request)
    {

        $shop =Shop::find($request->get('id'));
        $categories =MenuCategory::where('shop_id',$shop->id)->get();
        $distance =mt_rand(500,5000);
        $shops=[
            "id"=>$shop->id,
            'shop_name'=>$shop->shop_name,
            'shop_img'=>$shop->shop_img,
            'shop_rating'=>$shop->shop_rating,
            "service_code"=> 4.6,// 服务总评分
            "foods_code"=>4.4,// 食物总评分
            "high_or_low"=>true,// 低于还是高于周边商家
            "h_l_percent"=>30,// 低于还是高于周边商家的百分比
            "brand"=> $shop->brand==1?true:false,
            "on_time"=>$shop->on_time==1?true:false,
            "fengniao"=>$shop->fengniao==1?true:false,
            "bao"=>$shop->bao==1?true:false,
            "piao"=>$shop->piao==1?true:false,
            "zhun"=>$shop->zhun==1?true:false,
            "start_send"=>$shop->start_send,
            "send_cost"=>$shop->send_cost,
            "distance"=>$distance,
            "estimate_time"=>$distance/200,
            "notice"=>$shop->notice,
            "discount"=>$shop->discount,
        ];

        $shops["evaluate"]=[
            [
                "user_id" =>12344,
                "username"=>"w******k",
                "user_img"=>"http://elebmy.oss-cn-hongkong.aliyuncs.com/upload/cI4kThDFJdL4UxBOgdUUm8IZtradsTgQW4qQY6VD.jpeg",
                "time"=>"2017-2-22",
                "evaluate_code"=>3,
                "send_time"=>30,
                "evaluate_details"=>"好吃",
            ], [
                "user_id" =>12344,
                "username"=>"w******k",
                "user_img"=>"http://elebmy.oss-cn-hongkong.aliyuncs.com/upload/cI4kThDFJdL4UxBOgdUUm8IZtradsTgQW4qQY6VD.jpeg",
                "time"=>"2017-2-22",
                "evaluate_code"=>2,
                "send_time"=>20,
                "evaluate_details"=>"不怎么好吃",
            ], [
                "user_id" =>12344,
                "username"=>"w******k",
                "user_img"=>"http://elebmy.oss-cn-hongkong.aliyuncs.com/upload/cI4kThDFJdL4UxBOgdUUm8IZtradsTgQW4qQY6VD.jpeg",
                "time"=>"2017-2-22",
                "evaluate_code"=>4,
                "send_time"=>34,
                "evaluate_details"=>"好吃",
            ],
        ];
        $a=[];
        foreach ($categories as &$category){
            $menus =Menu::where('category_id',$category->id)->get();
            unset($category['id'],$category['updated_at'],$category['created_at'],$category['shop_id']);

            foreach ($menus as &$menu){
                $menu['goods_id']=$menu['id'];
                $menu['month_sales']=mt_rand(10,500);
                $menu['satisfy_rate']=mt_rand(80,100);
                unset($menu['id'],$menu['created_at'],$menu['updated_at'],$menu['shop_id'],$menu['category_id']);
            }
            $category['goods_list'] =$menus;
            $a[]=$category;
        }
        $shops['commodity'] =$a;
        $category['goods_list']=$menus;
        return json_encode($shops);
    }
    //短信接口
    public function sms(){

        $tel=request()->input('tel');
        $rand=random_int(1000,9999);
        $params = array ();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIhcsUNAoer6lJ";
        $accessKeySecret = "m0GvWXBZNGtZ0c70KViuQgrmNDCtk8";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "饶毅";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_140515029";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "code" => $rand
//        "product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new \App\SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );
        Redis::set('code'.$tel,$rand);
        Redis::Expire('code'.$tel,300);
    }
    //注册接口
    public function regist(Request $request){
        $validator=Validator::make($request->all(),[
            'username'=>'required|unique:members|',
            'tel'=>'required|max:11|max:11',
            'sms'=>'required',
            'password'=>'required|max:16|min:6'
        ],[
            'username.required'=>"用户名不能为空",
            'username.unique'=>"此用户名已被注册",
            'tel.required'=>"电话号码不能为空",
            'tel.max'=>"电话号码必须为11位",
            'tel.min'=>"电话号码必须为11位",
            'sms.required'=>"请填写验证码",
            'password.required'=>"密码不能为空",
            'password.max'=>"密码超出长度",
            'password.min'=>"密码过短"
        ]);
        if ($validator->fails()){
            return  [
                'status'=>'false',
                'message'=>$validator->errors()->first()
            ];
        }
        $tel=Redis::get('code'.$request->tel);
        if ($request->input('sms')!=$tel){
            return [
                'status'=>'false',
                'message'=>"验证码错误"
            ];
        }
        $input=$request->except('sms');
        $input['password'] = bcrypt($input['password']);
        Member::create($input);
        return response()->json(['status'=>true,'message'=>'注册成功']);
    }
    //登录接口
    public function loginCheck(Request $request){
//        $member=Member::where('username',$request->input('name'))->first();
//        if (!$member){
//            return response()->json([
//                'status'=>false,
//                'message'=>'用户不存在'
//            ]);
//        }
        if(Auth::attempt(['username'=>$request->name,'password'=>$request->password])){
            return response()->json([
                'status'=>"true",
                'user_id'=>Auth::id(),
                'username'=>Auth::user()->username,
                'message'=>'登录成功'
            ]);
        }else{
            return response()->json([
                'status'=>"false",
                'message'=>'登录失败'
            ]);

        };
//        if (!Hash::check($request->input('password'),$member->password)){
//                return response()->json([
//                    'status'=>false,
//                    'message'=>'登录失败'
//                ]);
//        }
//        return response()->json(['status'=>"true",'message'=>'登录成功','user_id'=>$member->id,'username'=>$member->username]);
//        $member=Member::where('username',$request->input('username'))->get();
//        dd($member);
    }
    //添加收货地址
    public function  addAddress(Request $request){
        $id=Auth::user()->id;
        $count=Address::where('is_default',1)->count();
        if ($count){
            $default=0;
        }else{
            $default=1;
        }
        Address::create([
            'user_id'=>$id,
            'province'=>$request->input('provence'),
            'city'=>$request->input('city'),
            'county'=>$request->input('area'),
            'address'=>$request->input('detail_address'),
            'name'=>$request->input('name'),
            'tel'=>$request->input('tel'),
            'is_default'=>$default
        ]);
       return response()->json([
            'status'=>"true",
            'message'=>"添加成功!"
        ]);
    }
    //收货地址列表
    public function addresslist(){
        $address=Address::where('user_id',Auth::id())->get();
        foreach ($address as &$v){
            $v->provence=$v->province;
            $v->area=$v->county;
            $v->detail_address=$v->address;
        }


        return $address;
    }
    //修改收货地址
    public function address(Request $request){
        $address=Address::where('id',$request->id)->first();
        $address['provence']= $address['province'];
        $address['detail_address']= $address['address'];
        $address['area']= $address['county'];
        return response()->json($address);
    }
    //保存收货地址的修改
    public function editAddress(Request $request){
        $address=Address::where('id',$request->id)->first();
        $address->update([
            'name'=>$request->input('name'),
            'tel'=>$request->input('tel'),
            'province'=>$request->input('provence'),
            'city'=>$request->input('city'),
            'county'=>$request->input('area'),
            'address'=>$request->input('detail_address')
        ]);
       return response()->json([
            'status'=>"true",
            'message'=>"修改成功!"
        ]);
    }
    //购物车的保存
    public function addCart(Request $request){
        $member_id = Auth::id();
        $cart = [];
        foreach ($request->goodsList as $k => $v) {
            $cart[$v]['goods_id'] = $v;
            $cart[$v]['amount'] = $request->goodsCount[$k];
            $cart[$v]['user_id'] = $member_id;
        }
        Cart::insert($cart);
        return ["status" => "true", "message" => "添加成功"];
    }
    //订单的生成
    public function addOrder(Request $request){
//        user_id	int	用户ID     ->Auth::user()->id;
        $id=Auth::id();
//shop_id	int	商家ID		在购物车表里根据用户ID找到对应的商品ID,再根据商品ID到菜品表里找到所属商家
        $cart=Cart::where('user_id',$id)->first();
        $goods_id=$cart->goods_id;
        $menu=Menu::where('id',$goods_id)->first();
        $shop_id=$menu->shop_id;
//sn	string	订单编号			当前时间戳加上一个随机数
        $time=date("YmdHi",time());
        $str=mt_rand(10000,99999);
        $sn=$time.$str;
//province	string	省	根据返回的地图ID
        $address=Address::where('id',$request->address_id)->first();
        $province=$address->province;
//city	string	市		根据返回的地图ID
        $city=$address->city;
//county	string	县		根据返回的地图ID
        $county=$address->county;
//address	string	详细地址		根据返回的地图ID
        $area=$address->address;
//tel	string	收货人电话	根据返回的地图ID
        $tel=$address->tel;
//name	string	收货人姓名	根据返回的地图ID
        $name=$address->name;
//total	decimal	价格		根据购物车表中goods_id找到对应的菜品价格乘以购物车表中的数量
        $cart_ids=Cart::where('user_id',$id)->pluck('goods_id');
        $price = 0;
        foreach ($cart_ids as $cart_id){
            $price += Menu::where('id',$cart_id)->value('goods_price');
        }
//status	int	状态(-1:已取消,0:待支付,1:待发货,2:待确认,3:完成)   默认为待付款
//created_at	datetime	创建时间
//out_trade_no	string	第三方交易号（微信支付需要）     生成随机数
        $time=time();
        $out_trade_no=mt_rand(1000,9999);
        $orders=Order::create([
            'user_id'=>$id,
            'shop_id'=>$shop_id,
            'sn'=>$sn,
            'province'=>$province,
            'city'=>$city,
            'county'=>$county,
            'address'=>$area,
            'tel'=>$tel,
            'name'=>$name,
            'total'=>$price,
            'created_time'=>$time,
            'status'=>0,
            'out_trade_no'=>$out_trade_no
        ]);
//        $cart=Cart::where('user_id',$id)->get();
//        $ms=Menu::where('id',$cart->goods_id)->get();
        $goods = Cart::where('user_id',$id)->get();
        foreach ($goods as $good) {
            $menu = Menu::where('id', $good->goods_id)->first();
          //  $orders=Order::where('user_id',$id)->get();
//            foreach ($orders as $order){
////                $order_id=$order->id;
////            }
/// d
            $data = [
                'order_id' => $orders->id,
                'goods_id' => $good->goods_id,
                'amount' => $good->amount,
                'goods_name' => $menu->goods_name,
                'goods_img' => $menu->goods_img,
                'goods_price' => $menu->goods_price
            ];
            OrderGoods::create($data);
        }
//goods_img	string	商品图片
//goods_price	decimal	商品价格
        //创建订单后删除购物车中的信息
        Cart::where('user_id',$id)->delete();
        $member=Member::where('id',$id)->first();
//        $tel=$member->tel;
        $shop=Order::where('user_id',$id)->first();
        $shop_name=Shop::where('id',$shop->shop_id)->first();
        $emails=User::where('shop_id',$shop_name->id)->first();
        $email=$emails->email;
        $name=$shop_name->shop_name;
        $tel=Member::where('id',Auth::id())->pluck('tel');
//        return $tel;
//        $rand=random_int(1000,9999);
        $params = array ();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIhcsUNAoer6lJ";
        $accessKeySecret = "m0GvWXBZNGtZ0c70KViuQgrmNDCtk8";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "饶毅";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_140737221";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "code" => $name
//        "product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new \App\SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );



        //发邮件

//        Mail::send('您有新的饿了吧订单',function ($message) use($email){
//            $message->subject('订单提醒');
//            $message->to($email);
//            $message->from('371154371@qq.com','371154371');
//        });






        return response()->json(['status'=>true,'message'=>'新增订单成功']);

    }
    //订单列表
    public function orderList(){
        /**
         * "order_code": 订单号
         * "order_birth_time": 订单创建日期
         * "order_status": 订单状态
         * "shop_id": 商家id
         * "shop_name": 商家名字
         * "shop_img": 商家图片
         * "goods_list": [{//购买商品列表
         * "goods_id": "1"//
         * "goods_name": "汉堡"
         * "goods_img": "http://www.homework.com/images/slider-pic2.jpeg"
         * "amount": 6
         * "goods_price": 10
         * }]
         */
        $id=Auth::id();
        $orders=Order::where('user_id',$id)->get()->toArray();
        foreach ($orders as &$order){
            $order['shop_name'] = Shop::where('id',$order['shop_id'])->value('shop_name');
            $order['shop_img'] = asset(Shop::where('id',$order['shop_id'])->value('shop_img'));
            $order['order_code'] = $order['sn'];
            $order['order_birth_time'] = $order['created_at'];
            $order['order_status'] = $order['status'];
            $order['order_price'] = $order['total'];
            $order['order_address'] = $order['address'];
            $order['goods_list'] = OrderGoods::where('order_id',$order['id'])->get();
        }
        return response()->json($orders);
    }
    //修改密码
    public function changePassword(Request $request){
        $oldpassword=Member::where('id',Auth::id())->value('password');
        if (!Hash::check($request->oldPassword,$oldpassword)){
            return ["status" => "false", "message" => "原密码错误"];
        }
        Member::where('id',Auth::id())->update(['password'=>bcrypt($request->get('newPassword'))]);
        return ["status" => "true", "message" => "修改成功"];
    }
    //获取购物车数据
    public function Cart()
    {
        $ShoppingCart = Cart::where('user_id', Auth::id())->get();
        $cart = [];
        $cart['totalCost'] = 0;
        $goods_list = [];
        $cart['goods_list'] = [];
        foreach ($ShoppingCart as $v) {
            $goods_list['goods_id'] = $v['goods_id'];
            $goods_list['amount'] = $v['amount'];
            $goods = Menu::find($v['goods_id']);
            $goods_list['goods_price'] = $goods['goods_price'];
            $goods_list['goods_name'] = $goods['goods_name'];
            $goods_list['goods_img'] = $goods['goods_img'];
            $cart['totalCost'] += $goods_list['amount'] * $goods_list['goods_price'];
            $cart['goods_list'][] = $goods_list;
        }
        return json_encode($cart);
    }
    //获取指定订单数据接口
    public function order(){
        return  '[
         {
        "id": "1",
        "order_code": "0000001",
        "order_birth_time": "2017-02-17 18:36",
        "order_status": "代付款",
        "shop_id": "1",
        "shop_name": "上沙麦当劳",
        "shop_img": "http://www.homework.com/images/shop-logo.png",
        "goods_list": [{
            "goods_id": "1",
            "goods_name": "汉堡",
            "goods_img": "http://www.homework.com/images/slider-pic2.jpeg",
            "amount": 6,
            "goods_price": 10
        }, {
            "goods_id": "1",
            "goods_name": "汉堡",
            "goods_img": "http://www.homework.com/images/slider-pic2.jpeg",
            "amount": 6,
            "goods_price": 10
        }],
        "order_price": 120,
        "order_address": "北京市朝阳区霄云路50号 距离市中心约7378米北京市朝阳区霄云路50号 距离市中心约7378米"
    }';
    }
}

