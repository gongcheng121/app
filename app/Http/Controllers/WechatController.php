<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2015/5/25
 * Time: 14:40
 */

namespace App\Http\Controllers;


use App\Model\WechatInfo;
use App\Model\WechatMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use EasyWeChat\Foundation\Application;
use Illuminate\Support\Facades\URL;

class WechatController extends BaseController {

    protected $app;
    public function __construct(Request $request){
        $host = $request->getHttpHost();
        if($host!="app.iyaxin.com"){
            return response('not match','403');
        }

        parent::__construct();
    }
    public function jsdk($key,Request $request){
        if (!str_contains($request->server('HTTP_REFERER'), 'iyaxin.com')) {
            return response('404');
        }
        $wechat_info  = Cache::get('wechat_info_'.$key,function() use($key){
            try{
                $expiresAt = Carbon::now()->addDay(1);
                $wechatInfo =WechatInfo::where('key','=',$key)->firstOrFail();
                Cache::add('wechat_info_'.$key,$wechatInfo,$expiresAt);
                return  $wechatInfo;
            }catch (ModelNotFoundException $e){
                return response('Please contact the Administrator',403);
            }
        });
        $result =$wechat_info->toArray();
        $options = [
            'app_id'  => $result['appid'],         // AppID
            'secret'  => $result['secret'],     // AppSecret
            'token'   => $result['token'],          // Token
        ];
        $app = new Application($options);

        $js = $app->js;
        $debug = ($request->input('debug',false)==0? false:true);
        $js->setUrl($request->input('url'));
        $config =$js->config(array('onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ', 'onMenuShareWeibo'),$debug,false,true);
        $return['num'] = $result['count']+1;
        WechatInfo::where('key','=',$key)->update(['count' =>$result['count']+1]);
        if($request->input('iyaxin_app',false)){

            return response()->json($config)->setCallback($request->input('callback'));
        }
        return ($config);
    }
    public function card($key,Request $request){
        $result =WechatInfo::where('key','=',$key)->get()->toArray();
        $card = new Card($result[0]['appid'], $result[0]['secret']);
        $card = new Card('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');


        $cardList[] = $card->attachExtension('pgwjat_QVL29OYn9-kQi00efiaY8');
        return response()->json($cardList);
        $js->config(array('onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ', 'onMenuShareWeibo'),false,false);
        $return['wxconfig'] = $js->getSignaturePackage($request->url);
        $return['num'] = $result[0]['count']+1;
        WechatInfo::where('key','=',$key)->update(['count' =>$result[0]['count']+1]);
        return response()->json($return);
    }
    public function auth($key,Request $request,WechatInfo $wechatInfo){
        $result =$wechatInfo->getByKey($key);

        $return_url = $this->getRedirectUrl();
        if(Cache::has('return_url')){
            $return_url = Cache::get('return_url',$this->getRedirectUrl());
        }


        if(!Session::get('wechat_user_'.$key) || Session::get('fresh') ) {

            $appId = $result['appid'];
            $secret = $result['secret'];
            $config = [
                'app_id' => $appId,
                'secret' => $secret,
                'oauth' => [
                    'scopes' => ['snsapi_userinfo'],
                    'callback' => URL::full()
                ],
            ];

            $app = new Application($config);
            $oauth = $app->oauth;

            if ($request->code && $request->state) {
                try {

                    $user = $oauth->user()->toArray()['original'];
                } catch (\InvalidArgumentException $e) {
                    return redirect($request->fullUrlWithQuery(['code'=>'']));//清除code 并重新获取授权
//                    return 'please closed and try again';
                }


                $wechat_member = WechatMember::where('openid', '=', $user['openid'])->where('key', '=', $key)->get();
                if (!$wechat_member->toArray()) {
                    $data = $user;
                    $data['key'] = $key;
                    unset($data['privilege']);
                    unset($data['language']);
                    WechatMember::create($data);
                }
                Session::set('wechat_user_' . $key, $user);
            }
            return $oauth->redirect();


        }
        return redirect($return_url)->with('openid', Session::get('wechat_user_'.$key));
    }

    public function authcheck($key){
        Cache::put('return_url',$this->getRedirectUrl(),1);
        $result =WechatInfo::where('key','=',$key)->first()->toArray();
        $openid = Session::get('wechat_user_'.$key);
//        $openid = 'oVDTUjlt-L9-2UZZONiLutXPnM2M';
        $wechat_user_info = Session::get('wechat_user_'.$key);
//        $wechat_member = WechatMember::where('openid','=',$openid)->where('key','=',$key)->first();
//        $wechat_user_info = $wechat_member;
        return response()->json(['openid'=>$wechat_user_info['openid'],'key'=>$key,'wechat_user_info'=>$wechat_user_info]);
    }
}