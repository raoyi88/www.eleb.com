@extends('default')
@section('content')
    <div class="container">
        @include('_errors')
        <form action="{{ route('client.store') }}" method="post" class="form-group">
            {{ csrf_field() }}
            用户名: <input type="text" name="name" class="form-control"> <br>
            密码: <input type="password" name="password" class="form-control"> <br>
            验证码:<input id="captcha" class="form-control" name="captcha" >
            <img class="thumbnail captcha" src="{{ captcha_src('flat') }}" onclick="this.src='/captcha/flat?'+Math.random()" title="点击图片重新获取验证码">
            <br>
            <input type="submit" value="马上注册">


        </form>
    </div>
@endsection