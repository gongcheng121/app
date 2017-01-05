<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/27 0027
 * Time: 下午 5:27
 */

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Session;
use Overtrue\Wechat\RedPack;

class WechatPackController extends BaseController{


    public function getIndex(){
        $appId  = 'wx2a806911636ec3d7';
        $secret = '621304bc7c0047d9ae43b81f9224d00e';
        $mchId = '1259516401';
        $key = 'qkcqyKrYYj6g74u1NDI5wsexVjPwltD5';
        $k = '7cb551a19e58ed5524f2be99f251c405';
        $pack = new RedPack($appId,$secret,$mchId,$key);



        if(!Session::has('openid_'.$k)){
            return redirect('wechat/auth/'.$k);
        }
        $openid = Session::get('openid_'.$k);
//        $openid = 'oVDTUjlt-L9-2UZZONiLutXPnM2M';



        dd($pack->send($openid,100,'亚心网','亚心网12岁红包','感谢您对亚心网的支持','每天都可以参与哦！',$mchId));
    }
} 