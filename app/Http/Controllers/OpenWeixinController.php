<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/30 0030
 * Time: 下午 4:31
 */

namespace App\Http\Controllers;

use App\Model\WechatOauthApi;
use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;

class OpenWeixinController extends BaseController
{


//    App  多域名微信授权 接口程序
/*
 * example　url  GET  请求地址为 http://app.iyaxin.com/connect/oauth2/authorize?key=$key&redirect_uri=你的回调地址
 * return code  POSt 请求地址为 http://app.iyaxin.com/sns/oauth2/access_token
 * parameter  key , secret ,code
 * return userInfo (JSON)
 *
 */
    public function oauth2(Request $request)
    {
        $parameters = $request->route()->parametersWithoutNulls();
        $key = isset($parameters['one']) ? $parameters['one'] : (($request->key) ? $request->key : '');
        if (!$key) {
            return 'key is missing please connect administrator';
        }
        /** @var 微信公众号信息 $wechat_info */
        $wechat_info = getWecahtInfo($key);
        if (!$wechat_info) return response('Please contact the Administrator', 403);

        $appId = $wechat_info['appid'];
        $secret = $wechat_info['secret'];
        $config = [
            'app_id' => $appId,
            'secret' => $secret,
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => $request->fullUrlWithQuery(['redirect_uri' => '','code'=>'','state'=>'','m_redirect_uri'=>$request->redirect_uri])
            ],
        ];

        $app = new Application($config);
        $oauth = $app->oauth;


        try {
            $user = $oauth->user()->toArray()['original'];

            $data['code'] = $request->code;
            $data['user_info'] = serialize($user);
            $data['state'] = $request->state;
            $data['appid'] = $key;

            WechatOauthApi::firstOrCreate($data);

        } catch (\InvalidArgumentException $e) {
            return $oauth->redirect();
//                return redirect($request->fullUrlWithQuery(['code'=>'']));//清除code 并重新获取授权
        }


        $link = '?';
        if (strpos($request->m_redirect_uri, '?') >= 1) {
            $link = '&';
        }

        $code = $request->code;
        $redirect_uri = $request->m_redirect_uri . $link . 'code=' . $code;
        return redirect($redirect_uri);
    }

    public function token(Request $request)
    {
        $key =  $request->key;
        if (!$key) {
            return 'key is missing please connect administrator';
        }

        $wechat_info = getWecahtInfo($key);

        if($request->secret != $wechat_info['secret']){
            return response('sorry you don\'t have permission to access',403);
        }

        $code = $request->code;

        $oauthInfo = WechatOauthApi::where('code','=',$code)->first();

        if($oauthInfo){
            $user_info = unserialize($oauthInfo->toArray()['user_info']);
            return response()->json($user_info);
        }else{
            return response('please try again','400');
        }

    }

} 