<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/22 0022
 * Time: 上午 10:31
 */

namespace App\Http\Controllers;


use App\Model\LotteryCount;
use App\Model\LotteryHelpLog;
use App\Model\LotteryResult;
use App\Model\PackOrder;
use App\Model\WechatMember;
use App\Model\WechatUser;
use App\Model\WechatInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Overtrue\Wechat\Auth;

class WechatGameController extends BaseController{
    const MAX_COUNT  = 2;
    const STEP_COUNT = 10;
    public function __construct(){
        parent::__construct();
    }
    public function getIndex($key,Request $request){

        $user = Session::get('wechat_user'.$key);
        $openid = $user['openid'];
//        Session::set('openid_7cb551a19e58ed5524f2be99f251c405','oVDTUjlt-L9-2UZZONiLutXPnM2M');
//        dd(Session::has('openid_7cb551a19e58ed5524f2be99f251c405'));
    }
    /**
     * @param $key
     * @return \Illuminate\Http\RedirectResponse
     * 摇一摇抽奖入口
     */
    public function getHandlottery($key){
        if(!isset($key)) return;
        $result =WechatInfo::where('key','=',$key)->first()->toArray();
        $appId = $result['appid'];
        $secret = $result['secret'];
        $auth = new Auth($appId, $secret);
        $auth->authorize($to = null, $scope = 'snsapi_userinfo', $state = 'STATE');
        $data = $auth->user()->toArray();
        $user = Session::get('wechat_user'.$key);
        return redirect('game/lottery')->with('openid', $user['openid'])->with('key',$key);
    }
    public function getLottery(){
        $openid = Session::get('openid');
        $key = Session::get("key");
        if(!$openid){
            return "<h1>啊哦，您的链接出错了。请通过微信公众号获取链接</h1>";
        }
        Session::set('openid',$openid);
        $fromuser = $openid;
        $title = '摇一摇';
        $tOpenid = $openid;

        //获取今天参与次数
        $lottery_count = LotteryResult::where('openid','=',$openid)->where('add_time','>=',mktime(0,0,0))->count();
        $count = (self::MAX_COUNT - $lottery_count)>0 ? self::MAX_COUNT - $lottery_count :0 ;
        //获取我的中奖纪录
        $prize_log = LotteryResult::with('wechatMember')->where('openid','=',$openid)->where('prize_id','!=',8)->orderBy('add_time','DESC')->get();
        $lotteryHelpLogModel = new LotteryHelpLog();
        //我的帮助记录
        $help_log = $lotteryHelpLogModel->getLogCount($openid);

        //判断可以使用的帮助记录
        $log = $lotteryHelpLogModel->where('status','=',0)->where('tOpenid','=',$openid)->take(self::STEP_COUNT);

        $log_count = $log->count();
        $adcount = 0;
        if($log_count>=self::STEP_COUNT){
            $adcount = $log_count/self::STEP_COUNT;
            $count = $count+$adcount;
        }
        return view('game/handlottery/index',compact('key','fromuser','count','title','tOpenid','key','prize_log','help_log','log_count','adcount'));
    }
/*
 * 抽奖过程
 */
    public function postLottery(Request $request){
        if(!$request->openid && ($request->openid!=Session::get('openid')) ){
            return response()->json(['status'=>'-1','msg'=>'您的身份错误，请刷新后重试']);
        }
        //获取今天参与次数 如果少于0 则判断好友帮助数
        $lottery_count = LotteryResult::where('openid','=',$request->openid)->where('add_time','>=',mktime(0,0,0))->count();
        $result_count = (self::MAX_COUNT - $lottery_count)>0 ? self::MAX_COUNT - $lottery_count :0 ;
        $lotteryHelpLogModel = new LotteryHelpLog();
        $log = $lotteryHelpLogModel->where('status','=',0)->where('tOpenid','=',$request->openid)->take(self::STEP_COUNT);
        $log_count = $log->count();
        if($result_count==0 && $log_count>=self::STEP_COUNT){
            $result_count = $result_count+1;
            $log->update(['status' => 1]);
        }
        if($result_count<=0){
            //可参与的次数为0 返回异常
            return response()->json(['status'=>'-1','msg'=>'您今天的抽奖次数已经用完，快去邀请好友帮忙吧']);
        }
        $prize_arr = array(

            '12' => array('id'=>2,'prize'=>'300元美年大健康体检卡',             'v'=>15, 'count'=>200),
            '8' => array('id'=>9,'prize'=>'谢谢参与',                        'v'=>1000,   'count'=>1000000),
        );
        $count = LotteryCount::all()->toArray();

        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
            $c[$val['id']] = $val['count'];
        }
        foreach($count as $k=>$v){
            $p = $c[$v['prizeId']]-$v['count'];
            if($p<=0){
                unset($arr[$v['prizeId']]);
            }else{
                //TODO 是否跟随奖品数量，降低概率
//                $arr[$v['prizeId']] = $c[$v['prizeId']]-$v['count'];
            }
        }
        //是否抽中过红包
        $red_count = LotteryResult::where('openid','=',$request->openid)->where('prize_id','=','12')->count();

        if($red_count>=1){
            //如果抽中过，则将其剔除
            unset($arr[12]);
        }else if($red_count==0 && $log_count>=10){
            //TODO  从未领取红包，并且获得好友帮助超过一定数量，则必中红包
           // $arr[8]=10000;
        }
        $rid = $this->getRand($arr); //根据概率获取奖项id
        $res = $prize_arr[$rid-1]; //中奖项
        $time = time();
        if($rid==8){
            //如果是红包
            $order_id = date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
            $data['mch_billno'] = '12595164'.$order_id;
            $data['mch_id'] = '1259516401';
            $data['re_openid'] =$request->openid;
            $data['amount'] = 100;
            $data['status'] = 0;
            $data['add_time']  = time();
            $data['real_ip'] = $this->getRealIp();
            PackOrder::create($data);
        }
        LotteryResult::create(['openid'=>$request->openid,'lottery'=>$res['prize'],'add_time'=>$time,'prize_id'=>$rid-1]);
        $Lottery_count = LotteryCount::firstOrCreate(['prizeId'=>$res['id'],'prize'=>$res['prize']]);
        $Lottery_count->increment('count',1);
        $result['prizeId'] = $rid-1;
        $result['prize'] = $res['prize'];
        $result['openid'] = $request->openid;

        return response()->json($result);
    }


    public function getHelplottery($tOpenid,Request $request){
        $key = $request->key;
        $user = Session::get('wechat_user'.$key);
        $openid = $user['openid'];
        if($openid == $tOpenid){
            // 自己点开的
           return redirect('game/lottery')->with('openid', $openid)->with('key',$k);
        }
        $tMemberInfo = WechatMember::where('openid','=',$tOpenid)->where('key','=',$key)->first();
        if($tMemberInfo){
            $tMemberInfo = $tMemberInfo->toArray();
        }
        $title = '帮好友'.$tMemberInfo['nickname'].'加油';
//        获取他的帮助记录
        $lotteryHelpLogModel = new LotteryHelpLog();
        $log = $lotteryHelpLogModel->getLogCount($tOpenid);
        $myCount = LotteryHelpLog::where('tOpenid','=',$tOpenid)->where('fOpenid','=',$openid)->get();//获取我帮助的次数

//        获取中奖记录
        $lotteryResult = LotteryResult::with('wechatMember')->where('prize_id','!=','8')->orderBy('add_time','DESC')->take('20')->get();

        return view('game/handlottery/help',compact('tMemberInfo','title','key','log','myCount','lotteryResult'));
    }

    public function postHelp(Request $request){
        $key = $request->key;
        $result =WechatInfo::where('key','=',$key)->first()->toArray();
        $k = $result['key'];
        if(!Session::has('openid_'.$k)){
            return response()->json(['status'=>-1,'msg'=>'身份出错，请重试']);
//            return redirect('wechat/auth/'.$k);
        }
        $fOpenid = Session::get('openid_'.$k);
//        $fOpenid = 'oVDTUjlt-L9-2UZZONiLutXPnM2M';
        $tOpenid = $request->openid;
        $add_time = time();
        $data['fOpenId'] = $fOpenid;
        $data['tOpenId'] = $tOpenid;
        $data['add_time'] = $add_time;
        $lotteryHelpLogModel= new LotteryHelpLog();
        $myCount = $lotteryHelpLogModel->getMyLogCount($tOpenid,$fOpenid);//获取我帮助的次数
        if($myCount>=1){
            return response()->json(['status'=>-1,'msg'=>'您已经帮助过了']);
        }else{
            LotteryHelpLog::create($data);
        }
        return response()->json(['status'=>0,'msg'=>'您成功的帮助了他，快去通知他吧']);

    }
    function getRand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);

        return $result;
    }

    public static function getRealIp() {
        $ip = "Unknown";
        if (isset($_SERVER["HTTP_X_REAL_IP"]) && !empty($_SERVER["HTTP_X_REAL_IP"])) {
            $ip = $_SERVER["HTTP_X_REAL_IP"];
        }
        elseif (isset($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]) && !empty($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"])) {
            $ip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
        }
        elseif (isset($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]) && !empty($HTTP_SERVER_VARS["HTTP_CLIENT_IP"])) {
            $ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
        }
        elseif (isset($HTTP_SERVER_VARS["REMOTE_ADDR"]) && !empty($HTTP_SERVER_VARS["REMOTE_ADDR"])) {
            $ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
        }
        elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }
        elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        }
        if( $ip == 'Unknown'){
            // 调试信息
            $ip = '127.0.0.1';
//            self:: debugErrorSendWx('获取不到ip地址', $_SERVER );
        }
        return $ip;
    }
} 