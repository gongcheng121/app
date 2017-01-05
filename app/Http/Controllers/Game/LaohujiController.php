<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/4 0004
 * Time: 下午 5:20
 */

namespace App\Http\Controllers\Game;


use App\Http\Controllers\BaseController;
use App\Model\WechatCardList;
use Illuminate\Http\Request;
use Overtrue\Wechat\Card;

class LaohujiController extends BaseController{
    public function __construct()
    {
        parent::__construct(); // TODO: Change the autogenerated stub
    }


    public function postGetAjax(Request $request){

        if(!$request->key){
            return '';
        }
        $openid=$request->wechat_id;
        $cardId = '';
        $key  = $request->key;
        $msg = '这次没有中奖，请加油';
        $prize = '';
        $rand = [rand(1,7),rand(1,7),rand(1,7)];
        if($rand[0]!=7 && ($rand[1]==$rand[0] && $rand[0]==$rand[2])){
            $rand[0]=7;
        }
//        $rand = [1,1,1];
        $return = [
            'success'=>1,
            'data'=>[
                'left'=>$rand[0],
                'middle'=>$rand[1],
                'right'=>$rand[2],
                'prize_type'=>$msg,
                'sn'=>'',
                'prize'=>$prize,
                'type'=>'0',

            ]
        ];
            $isprize = ($rand[0]==$rand[1] && $rand[2]==7 &&  $rand[2]==$rand[1]);
        if($isprize) {
            $cardId = 'pgwjat4lUl8v888njLnCOlJfXK-I';
            $msg = '恭喜您获得滑雪票一张';
            $data = [
                'openid'=>$openid,
                'cardid'=>$cardId,
                'key'=>$key,
            ];
            $wechatCardInfo = WechatCardList::firstOrNew($data);

            $prize = '滑雪票';

            $wechatCardInfo->save();
            $card = new Card('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');
            $cardList = $card->attachExtension($cardId);
            $result['cardList'] = $cardList;
            $result['card'] = true;
            $result['cardid'] = $wechatCardInfo->id;
            $result['status'] = 1;
            $result['msg']='正在跳转至卡券';

            $return['data']['prize_type'] = '滑雪票一张';
            $return['data']['type'] = '1';
            $return['data']['cardList'] = $result['cardList'];
        }

        return response()->json($return);
    }

    public function postGetCode(Request $request){
        $key = 'e20421fdbc4334f4620eb0bb5b3cc084';
        $openid = $request->openid;
        $cardId = 'pgwjat4lUl8v888njLnCOlJfXK-I';
        $cardInfo  = WechatCardList::where('key',$key)
            ->where('openid',$openid)
            ->where('cardid',$cardId);
        return response()->json($cardInfo->first());
    }


} 