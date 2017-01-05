<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/25 0025
 * Time: 下午 5:19
 */

namespace app\Http\Controllers;


use App\Model\WechatCardList;
use App\Model\WechatKeyVerify;
use App\Model\WechatMemberDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Overtrue\Wechat\Card;
use Overtrue\Wechat\Js;

class KeyController extends BaseController{



    public function getIndex($key,Request $request){
        $k='e20421fdbc4334f4620eb0bb5b3cc084';

        if(!Session::has('openid_'.$k)){
            return redirect('wechat/auth/'.$k);
        }
        $openid = Session::get('openid_'.$k);

//        Session::set('key',$key);
        $k='e20421fdbc4334f4620eb0bb5b3cc084';
        if(!Session::has('openid_'.$k)){
            return redirect('wechat/auth/'.$k);        }
        $title = '授权验证';
        return  view('keyverify',compact('title'));
    }

    public function postIndex($key,Request $request,WechatMemberDetail $wechatMemberDetail){
        $result = WechatKeyVerify::where('key',$key)->where('password',$request->password)->where('status','0')->first();

        if($result){
            Session::set('key',$key);
            return response()->json(['status'=>1,'url'=>url('key/card')]);
        }
        return response()->json(['status'=>0,'msg'=>'您的授权码无效']);
    }

    public function idcard(Request $request,WechatMemberDetail $wechatMemberDetail){
        $key = Session::get('key');
        $k='e20421fdbc4334f4620eb0bb5b3cc084';
        $openid = Session::get('openid_'.$k);
        WechatKeyVerify::where('key',$key)->update(['status'=>1,'openid'=>$openid]);
        $id = $wechatMemberDetail->firstOrCreate($request->all());
        if($id){
            return response()->json(['success'=>1,'msg'=>'ok']);
        }
        return response()->json(['success'=>0,'msg'=>'出错']);
    }
    public function card(){

        $k='e20421fdbc4334f4620eb0bb5b3cc084';
        if(!Session::has('openid_'.$k)){
            return redirect('wechat/auth/'.$k);
        }
        $js = new Js('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');
        $js->config(array('onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ', 'onMenuShareWeibo'),false,false);
        $card = new Card('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');
        $cardList = $card->attachExtension('pgwjat6HBf76Aj5P8y372GFbWUnk');
        $cardId = $cardList['cardId'];
        $cardExt = "cardExt :'" .($cardList['cardExt'])."'";
        $k='e20421fdbc4334f4620eb0bb5b3cc084';
        $openid = Session::get('openid_'.$k);
        $data = [
            'openid'=>$openid,
            'cardid'=>'pgwjat6HBf76Aj5P8y372GFbWUnk',
            'key'=>$k,
        ];

        $wechatCardInfo = WechatCardList::firstOrNew($data);
        $wechatCardInfo->save();
        return view('card',compact('js','cardId','cardExt','openid'));
    }
}