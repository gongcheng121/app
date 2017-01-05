<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/22 0022
 * Time: 上午 10:36
 */


namespace App\Http\Controllers;


use App\Commands\SendMessage;
use App\Model\CardEventLog;
use App\Model\WechatCardList;
use Illuminate\Http\Request;
use App\Model\WechatInfo;
use App\Model\WechatRequest;
use App\Model\WechatUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Queue;
use Overtrue\Wechat\Message;
use Overtrue\Wechat\Server;

class WechatApiController extends BaseController{
    public function __construct(){
        parent::__construct();
    }

    public function api($key,Request $request){

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
        if(!$wechat_info) return response('Please contact the Administrator',403);
        $result =$wechat_info->toArray();
        $appId = $result['appid'];
        $secret = $result['secret'];
        $token          = $result['token'];
        $encodingAESKey = $result['encodingAESKey'];
        $server = new Server($appId, $token, $encodingAESKey);
        // 监听所有类型
        $server->on('event', 'subscribe', function($event){
//            $news = Message::make('news')->items(function() {
//                $url  = 'http://app.iyaxin.com/game/helpcard/handle/e20421fdbc4334f4620eb0bb5b3cc084';
//                return array(
//                    Message::make('news_item')->title('冰雪游体验卡活动'),
//                    Message::make('news_item')->title('每天12点开始，快来参与领取冰雪游体验卡吧！'),
//                    Message::make('news_item')->title('查看活动说明')->url('http://mp.weixin.qq.com/s?__biz=MzA4NDUxOTU1NQ==&mid=400969733&idx=1&sn=c3bc5b0a6f3b1165b40d4f174966e022#rd'),
//                    Message::make('news_item')->title('现在点击进入')->url($url),
//                );
//            });
//            return $news;
        });

        $server->on('event',function($message) use($key){
            $data['to_user_name']   = $message->ToUserName;
            $data['from_user_name'] = $message->FromUserName;
            $data['create_time']    = $message->CreateTime;
            $data['msg_type']       = $message->MsgType;
            $data['card_id']        = $message->CardId;
            $data['user_card_code']        = $message->UserCardCode;
            $data['is_give_by_friend']        = $message->IsGiveByFriend;
            $data['outer_id']        = $message->OuterId;
            $data['event']        = $message->Event;
            $data['key']        = $key;
            $data['friend_user_name']        = is_array($message->FriendUserName) ? json_encode($message->FriendUserName) : $message->FriendUserName;
            if($data['event']=='user_get_card' || $data['event']=='user_del_card' ){
                CardEventLog::create($data);
            }

            if($data['event']=='user_get_card'){

                $xmlInput = file_get_contents('php://input');
                Queue::push(new SendMessage(['data'=>$xmlInput,'title'=>'数据源']));
                $openid = $data['from_user_name'];
                $cardid = $data['card_id'];
                $wechatCard=WechatCardList::where('openid',$openid)->where('key',$key)->where('cardid',$cardid);
                $rdata['status']=1;
                $rdata['card_code']=$data['user_card_code'];
                $wechatCard->update($rdata);
//                Queue::push(new SendMessage(['data'=>$data,'title'=>'事件接口']));
            }
        });
        $server->on('message', function($message) use($key){
            $data['to_user_name']   = $message->ToUserName;
            $data['from_user_name'] = $message->FromUserName;
            $data['create_time']    = $message->CreateTime;
            $data['msg_type']       = $message->MsgType;
            $data['content']        = $message->Content;
            $data['key']        = $key;
//            Queue::push(new SendMessage(['data'=>$data,'title'=>'MSG:'.$data['content']]));
            if($message->MsgType=='image') $data['pic_url'] = $message->PicUrl;
            WechatRequest::create($data);

//                if(strstr("滑雪票,体验卡,参加,如何,活动",$message->Content )){
////                    Queue::push(new SendMessage(['data'=>$data,'title'=>'响应关键词']));
//                    $news = Message::make('news')->items(function() use($message){
//                        $url  = 'http://app.iyaxin.com/game/helpcard/handle/e20421fdbc4334f4620eb0bb5b3cc084';
//                        return array(
//                            Message::make('news_item')->title('冰雪游体验卡活动'),
//                            Message::make('news_item')->title('每天12点开始，快来参与领取冰雪游体验卡吧！'),
//                            Message::make('news_item')->title('查看活动说明')->url('http://mp.weixin.qq.com/s?__biz=MzA4NDUxOTU1NQ==&mid=400969733&idx=1&sn=c3bc5b0a6f3b1165b40d4f174966e022#rd'),
//                            Message::make('news_item')->title('现在点击进入')->url($url),
//                        );
//                    });
//                    return $news;
//                }
//            return Message::make('text')->content('感谢您的关注');
        });
        return $server->serve();
    }

}