<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21 0021
 * Time: 上午 10:08
 */

namespace App\Http\Controllers\Game;


use App\Commands\SendMessage;
use App\Http\Controllers\BaseController;
use App\Model\GameHelpInfo;
use App\Model\GameHelpLog;
use App\Model\WechatCardList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Overtrue\Wechat\Card;


class HelpcardController extends BaseController{

    const LEVAL_ONE=30;
    const LEVAL_TWO=40;
    const LEVAL_THREE=50;
    private $cardList;
    public function __construct(Request $request)
    {
        parent::__construct(); // TODO: Change the autogenerated stub
        $this->middleware('wechat');
        $this->cardList['leval_one']='pgwjat4fhx7SqdOeaOSRkcJXQ2ME';
        $this->cardList['leval_two']='pgwjat1ek_tS3DgMfWteNegT_x0s';
        $this->cardList['leval_three']='pgwjat7xkzIqsCvWZRNmumHVRBjs';
    }

    public function getHandle(Request $request){
        if(time()<=mktime(11,59,0)){
            return redirect('http://mp.weixin.qq.com/s?__biz=MzA4NDUxOTU1NQ==&mid=400969733&idx=1&sn=c3bc5b0a6f3b1165b40d4f174966e022#rd');
        }

        if(Cache::has('stop')){
            return redirect('http://mp.weixin.qq.com/s?__biz=MzA4NDUxOTU1NQ==&mid=400969325&idx=1&sn=43869da9a7e4e293dc1da6ec84860628#rd');
        }

        $key = $request->key;
        return redirect('game/helpcard/home')->with('code_key',$key);
    }

    public function getHome(Request $request,GameHelpInfo $gameHelpInfo , GameHelpLog $gameHelpLog){
        if(!Session::has('code_key')){
            return response('no auth',403);
//            如果没有授权code_key 则是通过非公众号链接
        }
        $key = Session::get('code_key');
        $wechat_user_info = Session::get('wechat_user_'.$key);
        $wechat_user_info['crypt_open_id']= Crypt::encrypt($wechat_user_info['openid']);
        $has_get = false;
        $wechatCardListInfo = WechatCardList::whereIn('cardid',array_flatten($this->cardList))->where('openid',$wechat_user_info['openid'])->where('status',1);
        if($wechatCardListInfo->count()>=1){
            //已经领取过 其中任意的卡券
            $has_get=true;
            $wechatCardListInfoDetail = $wechatCardListInfo->first();
        }

        //获取该砍价的详细信息
        $data = [
            'openid'=>$wechat_user_info['openid'],
        ];
        $gameHelpInfoDetail = $gameHelpInfo->firstOrCreate($data);
        $count = $gameHelpInfoDetail['help_count'];
        $now_step = $count>=self::LEVAL_THREE? 3 : ($count>=self::LEVAL_TWO? 2 : ($count>=self::LEVAL_ONE  ? 1:0));


        $description[1]='您可以领取1张6天有效期的滑雪券';
        $description[2]='您可以领取1张8天有效期的滑雪券';
        $description[3]='您可以领取1张10天有效期的滑雪券';
        if($gameHelpInfoDetail['help_count']>=self::LEVAL_ONE){
        //当用户具备领取资格才查询是否领取


        }
        //获取是否有自己的帮助记录
        if(!isset($gameHelpInfoDetail->id)){
            //新增  目前只有一个帮助
            $gameHelpInfoDetail->help_count = 0;
            $gameHelpInfoDetail->save();
        }

        $own_log = $gameHelpLog->where('openid',$wechat_user_info['openid'])->where('to_openid',$wechat_user_info['openid'])->where('kid',$gameHelpInfoDetail->id)->count();

        $help_log_list = Cache::get('help_log'.$gameHelpInfoDetail->id,function() use($gameHelpInfoDetail,$gameHelpLog,$wechat_user_info){
            $help_log_list = $gameHelpLog->where('kid',$gameHelpInfoDetail->id)->where('to_openid','=',$wechat_user_info['openid'])->where('kid',$gameHelpInfoDetail->id)->with('WechatMember')->orderBy('id','DESC')->get();
            Cache::put('help_log'.$gameHelpInfoDetail->id,$help_log_list,Carbon::now()->addMinute(10)->diffInMinutes());
            return $help_log_list;
        });
        /*
        if($wechat_user_info['openid']=='ogwjat13ZVguDIq_cpljTOjMpb_k'){
            $now_step = 3;
            $has_get = false;
        }*/

        return view('game/card/info',compact('key','wechat_user_info','gameHelpInfoDetail','own_log','help_log_list','now_step','description','has_get','wechatCardListInfoDetail'));
    }

    public function getShare($key,Request $request,GameHelpInfo $gameHelpInfo , GameHelpLog $gameHelpLog){
        if(Cache::has('stop')){
            return redirect('http://mp.weixin.qq.com/s?__biz=MzA4NDUxOTU1NQ==&mid=400969325&idx=1&sn=43869da9a7e4e293dc1da6ec84860628#rd');
        }
//        显示分享到朋友圈后点开的页面
        $sid = $request->sid;
        $kid = $request->kid;//砍价详情ID
        $to_openid = Crypt::decrypt($sid);//原分享用户OpenId

        $gameHelpInfoModel = $gameHelpInfo->where('id',$kid)->where('openid',$to_openid)->with('WechatMember');
        $gameHelpInfoDetail = $gameHelpInfoModel->first();
        $gameHelpLogModel = $gameHelpLog->where('kid',$kid);
        /*
         * 第一步：判断是否是自己点开的分享页面
         * 如果是自己点开的则跳转会自己的主页
         * 否则显示帮助砍价页面
         * */
        $wechat_user_info = Session::get('wechat_user_'.$key);
        if($wechat_user_info['openid'] == $to_openid){
            //我自己点开的分享链接
            $log_list = $gameHelpLogModel->get()->toArray();//我的帮助列表·
//            return "自己点开的";
            return redirect('game/helpcard/handle/'.$key);
        }
        $wechat_user_info['crypt_open_id'] = Crypt::encrypt($wechat_user_info['openid']);
        $gameHelpInfoDetail['wechatMember']['crypt_open_id'] = Crypt::encrypt($gameHelpInfoDetail['wechatMember']['openid']);
        //获取我帮助他的记录
//        $log  = $gameHelpLogModel->where('openid',$wechat_user_info['openid'])->where('to_openid','=',$to_openid)->count();
        $help_log_list = $gameHelpLogModel->where('to_openid','=',$to_openid)->where('kid',$kid)->with('WechatMember')->orderBy('id','DESC')->get();
        return view('game/card/help',compact('gameHelpInfoDetail','key','to_openid','wechat_user_info','help_log_list','sid'));
    }



    /*
     * 投票，只能投票一次
     * */
    public function postHelp(Request $request,GameHelpInfo $gameHelpInfo , GameHelpLog $gameHelpLog){
        $data = ['status'=>0,'msg'=>''];
        $kid = $request->input('kid');
        $openid = Crypt::decrypt($request->input('openid'));
        $to_openid = Crypt::decrypt($request->input('to_openid'));
        $data=['kid'=>$kid,'openid'=>$openid,'to_openid'=>$to_openid];
        $gameHelpLogs = $gameHelpLog->firstOrNew($data);
        if($gameHelpLogs->id){
            $data=['status'=>'-1','msg'=>'数据已经存在，请勿重复提交'];
        }else{
            $gameHelpLogs->save();
            $gameHelpInfo->where('id',$kid)->increment('help_count');
            $data = ['status'=>1,'msg'=>'success'];
            return response()->json($data);
        }
        return response()->json($data);
    }

    public function postLog(Request $request){
        $kid =$request->kid;
        $log = Cache::get('kanjia_help_log'.$kid,function()use($kid){
            KanjiaHelpLog::where('kid',$kid)->get();
        });
        return response()->json($log);
    }

    public function postTicket(Request $request,GameHelpInfo $gameHelpInfo){
        $openid = $request->openid;
        $key = $request->key;
        $kid = $request->kid;
        $cardId = $this->cardList['leval_one'];

        $openid = Crypt::decrypt($openid);//原分享用户OpenId

        $wechat_user_info = Session::get('wechat_user_'.$key);
        if(!$wechat_user_info['openid']==$openid){
            return 'error oauth';
        }

        $gameHelpInfoModel = $gameHelpInfo->where('id',$kid)->where('openid',$openid)->with('WechatMember');
        $gameHelpInfoDetail = $gameHelpInfoModel->first();
        if(!$gameHelpInfoDetail){
            return 'error';
        }

        $help_count = $gameHelpInfoDetail->help_count;
        /*
        if($wechat_user_info['openid']=='ogwjat13ZVguDIq_cpljTOjMpb_k'){
            $now_step = 3;
            $help_count = 9999;
        }*/
        $return = ['status'=>0,'data'=>'','msg'=>''];
        if($help_count<self::LEVAL_ONE){
            $return['msg']='您还没有达到领取等级，邀请更多好友帮忙吧！';
            return response()->json($return);
        }
        if($help_count>=self::LEVAL_TWO){
            $cardId = $this->cardList['leval_two'];//等级2卡券
        }
        if($help_count>=self::LEVAL_THREE){
            $cardId = $this->cardList['leval_three'];//等级3卡券
        }
        $data = [
            'openid'=>$openid,
            'cardid'=>$cardId,
            'key'=>$key,
        ];
        $wechatCardInfo = WechatCardList::firstOrNew($data);
        $result=[];
        $can = true;

        if($wechatCardInfo->id){
            if($wechatCardInfo->status==0){
                //虽然领取过，但未成功领取，可以继续创建
                $can = true;
            }else{
                $return['msg']='您已经领取过了';
            }
        }else{
            //第一次创建
            $can = true;
        }

        if($can){
            $wechatCardInfo->save();
            $card = new Card('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');
            $cardList = $card->attachExtension($cardId);
            $result['cardList'] = $cardList;
            $result['card'] = true;
            $result['cardid'] = $wechatCardInfo->id;
            $result['status'] = 1;
            $result['msg']='正在跳转至卡券';
        }
        return response()->json($result);

    }

    public function getStop(){
        Cache::get('stop',function(){
            Cache::put('stop',true,$expiresAt = Carbon::now()->addHour(10)->diffInMinutes());
            return true;
        });
        return response('stop ok');
    }

    public function getStart(){
        Cache::forget('stop');
        return response('start ok');

    }

}