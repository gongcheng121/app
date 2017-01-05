<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/25 0025
 * Time: 下午 5:54
 */

namespace App\Http\Controllers;



use App\Commands\SendMessage;
use App\Model\WechatInfo;
use App\Model\WechatOauthApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Overtrue\Wechat\Auth;
use Overtrue\Wechat\Utils\Http;

class WechatOauthApiController extends BaseController {


    function __construct()
    {
        parent::__construct();
    }

    public function oauth(Request $request){
//        $result =WechatInfo::where('key','=','47b730b304aa9b5aae7d2a2faf201d26')->first()->toArray();
        $result =WechatInfo::where('appid','=',$request->appid)->first()->toArray();
        $appId = $result['appid'];
        $secret = $result['secret'];
        $link = '?';
        if(strpos($request->redirect_uri,'?')>=1){
            $link = '&';
        }
//        if(!self::getCache('code') && !self::getCache('openid')){
            $auth = new Auth($appId, $secret);
            $auth->authorize($to = null, $scope = 'snsapi_userinfo', $state = 'STATE');
            $user = $auth->user()->toArray();
            $data['code'] = $request->code;
            $data['user_info'] = serialize($user);
            $data['state'] = $request->state2;
            $data['appid'] = $request->appid;
            WechatOauthApi::firstOrCreate($data);
            self::setCache('code',$request->code);
            self::setCache('openid',$user['openid']);
            $redirect_uri = $request->redirect_uri.$link.'code='.$request->code;
//        }else{
//            $redirect_uri = $request->redirect_uri.$link.'code='.self::getCache('code');
//
//        }
//dd($redirect_uri);
        return redirect($redirect_uri);
    }

    public function token(Request $request){
        $code = $request->code;
        $oauthInfo = WechatOauthApi::where('code','=',$code)->first();
        if($oauthInfo){
            $user_info = unserialize($oauthInfo->toArray()['user_info']);
            return response()->json($user_info);
        }else{
            return response('401');
        }
    }

    private static function  getCache($key){
       return Cache::get($key,function(){
           return false;
       });
    }

    private static function setCache($key,$value,$time=1){
        $expiresAt = Carbon::now()->addMinutes($time);
        Cache::put($key,$value,$expiresAt);
    }

}