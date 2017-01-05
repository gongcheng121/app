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

use App\Model\WechatCardList;
use App\Model\WechatMember;
use App\Model\WechatMemberDetail;

use App\Model\WechatInfo;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

use Overtrue\Wechat\Card;

class WechatGameController extends BaseController
{
    const MAX_COUNT = 10;
    const STEP_COUNT = 2;

    public function __construct()
    {
//        $this->middleware('wechat');
        parent::__construct();
    }

    public function getIndex(Request $request)
    {
//        Cache::put("CardNum_17",32,60);
        $cardId = 'pgwjatxdwr4tcO0X35OItsERZlmE';
        $card = new Card('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');
        $cardList = $card->attachExtension($cardId);
//        dd($cardList);
//        dd( WechatCardList::where('status',1)->where('created_at','>=',date('Y-m-d H'))->count());
//        $user = Session::get('wechat_user'.$key);
//        $openid = $user['openid'];
        Session::set('openid_e20421fdbc4334f4620eb0bb5b3cc084', 'ogwjat13ZVguDIq_cpljTOjMpb_k2');
//        dd(Session::has('openid_7cb551a19e58ed5524f2be99f251c405'));
    }

    /**
     * @param $key
     * @return \Illuminate\Http\RedirectResponse
     * 摇一摇抽奖入口
     */
    public function getHandlottery($key)
    {
//        return response('<h1>滑雪体验卡已经抢完，请关注下一次活动时间</h1>');
        if (!Session::has('openid_' . $key)) {
            return redirect('wechat/auth/' . $key);
        }
        $openid = Session::get('openid_' . $key);
//        return redirect('pingtu/lottery')->with('openid', $openid)->with('key',$key);
        return redirect('game/lottery')->with('openid', $openid)->with('key', $key);
    }

    public function getLottery(Request $request)
    {

        $openid = Session::get('openid');
        $key = Session::get("key");
        if (!$openid) {
            return "<h1>啊哦，您的链接出错了。请在在众号中，点击菜单重新获取链接</h1>";
        }
        if (!self::checkTime()) {
//            return "<h1>活动12点开启</h1>";
        }
        $fromuser = $openid;
        $title = '摇一摇抽奖';
        $tOpenid = $openid;
        //获取今天参与次数
        $lottery_count = LotteryResult::where('openid', '=', $openid)->where('add_time', '>=', mktime(0, 0, 0))->count();
//        $share_count = Cache::get('share_count_'.$openid,function(){
//            return 0;
//        });

//        if($share_count<=1)  {
//            $lottery_count = $lottery_count-$share_count;
//        }

        $count = (self::MAX_COUNT - $lottery_count) > 0 ? self::MAX_COUNT - $lottery_count : 0;

        $adcount = 0;
        $isDetail = WechatMemberDetail::where('openid', $fromuser)->count();
        return view('game/handlottery/index', compact('isDetail', 'key', 'fromuser', 'count', 'title', 'tOpenid', 'key', 'prize_log', 'help_log', 'log_count', 'adcount'));
    }


    public function postDetail(Request $request)
    {

        $data['true_name'] = $request->get('name', false);
        $data['openid'] = $request->get('openid', false);
        $data['id_card'] = $request->get('idcard', false);
        if ($data['true_name'] == false || $data['openid'] == false || $data['id_card'] == false) {
            return response()->json(['error' => 1, 'msg' => '请填写完整']);
        }

        $count = WechatMemberDetail::where('id_card', $data['id_card'])->count();

        if ($count >= 1) {
            return response()->json(['error' => 1, 'msg' => '身份证号已被绑定']);
        }


        WechatMemberDetail::firstOrCreate($data);
        return 1;


    }

    /*
     * 抽奖过程
     */
    public function postLottery(Request $request)
    {
        if (!$request->openid) {
            return response()->json(['status' => '-1', 'msg' => '您的身份错误，请刷新后重试']);
        }

        $openid = $request->openid;
        $isDetail = WechatMemberDetail::where('openid', $openid)->count();
        if ($isDetail == 0) {
            return response()->json(['status' => '-2', 'msg' => '您的身份错误，请刷新后重试']);
        }
//        $share_count = Cache::get('share_count_'.$request->openid,function(){
//            return 0;
//        });
        //获取今天参与次数 如果少于0 则判断好友帮助数
        $lottery_count = LotteryResult::where('openid', '=', $request->openid)->where('add_time', '>=', mktime(0, 0, 0))->count();
//        if($share_count<=1) {
//            $lottery_count = $lottery_count-$share_count;
//        }
        $result_count = (self::MAX_COUNT - $lottery_count) > 0 ? self::MAX_COUNT - $lottery_count : 0;

        Cache::increment('share_count_' . $request->openid);
        if ($result_count <= 0) {
            //可参与的次数为0 返回异常
            return response()->json(['status' => '-1', 'msg' => '您今日的抽奖次数已经用完']);
        }
        $prize_list = [
            '0' => '滑雪券',
            '8' => '谢谢参与'
        ];


        $prize_arr = array(
            '1' => array('id' => 2, 'prize' => $prize_list[0], 'v' => 100, 'count' => 2600),
            '8' => array('id' => 9, 'prize' => $prize_list[8], 'v' => 100, 'count' => 10000000),
        );
        $arr = [];
        $count = LotteryCount::all()->toArray();
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
            $c[$val['id']] = $val['count'];
        }
        if (sizeof($count) > 1) {
            foreach ($count as $k => $v) {
                if (isset($c[$v['prizeId']])) {
                    $p = $c[$v['prizeId']] - $v['count'];
                    if ($p <= 0) {
                        unset($arr[$v['prizeId']]);
                    }
                }
            }
        }
        //是否抽中过红包
        $red_count = LotteryResult::where('openid', '=', $request->openid)->where('prize_id', '=', '2')->count();
        if ($red_count >= 1) {
            //如果抽中过，则将其剔除
            unset($arr[2]);
        } else if ($red_count == 0) {
            //TODO  从未领取红包，并且获得好友帮助超过一定数量，则必中红包
            // $arr[8]=10000;
        }
        $rid = $this->getRand($arr); //根据概率获取奖项id
        $res = $prize_arr[$rid - 1]; //中奖项
        $time = time();
        if ($rid == 2) {
            $cardId = 'pgwjat1TukOTEHDidoeWuxkaDkNM';
            $data = [
                'openid' => $openid,
                'cardid' => $cardId,
                'key' => $request->key,
            ];

            $wechatCardInfo = WechatCardList::firstOrNew($data);
            $can = false;
            if ($wechatCardInfo->id) {
                if ($wechatCardInfo->status == 0) {
                    //虽然领取过，但未成功领取，可以继续创建
                    $can = true;
                } else {
                    $result['msg'] = '您已经领取过了';
                }
            } else {
                //第一次创建
                $can = true;
                $wechatCardInfo->save();
            }

            $card = new Card('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');
            $cardList = $card->attachExtension('pgwjat1TukOTEHDidoeWuxkaDkNM');
            $result['cardList'] = $cardList;
            $result['card'] = $can;
        }
        LotteryResult::create(['openid' => $request->openid, 'lottery' => $res['prize'], 'add_time' => $time, 'prize_id' => $res['id']]);
        $Lottery_count = LotteryCount::firstOrCreate(['prizeId' => $res['id'], 'prize' => $res['prize']]);
        $Lottery_count->increment('count', 1);
        $result['prizeId'] = $res['id'];
        $result['prize'] = $res['prize'];
        $result['openid'] = $request->openid;
        $result['result_count'] = $result_count;
        $result['status'] = 1;
        return response()->json($result);
    }


    public function getHelplottery($tOpenid, Request $request)
    {
        $key = $request->key;
        $user = Session::get('wechat_user' . $key);
        $openid = $user['openid'];
        if ($openid == $tOpenid) {
            // 自己点开的
            return redirect('pingtu/lottery')->with('openid', $openid)->with('key', $key);
        }
        $tMemberInfo = WechatMember::where('openid', '=', $tOpenid)->where('key', '=', $key)->first();
        if ($tMemberInfo) {
            $tMemberInfo = $tMemberInfo->toArray();
        }
        $title = '帮好友' . $tMemberInfo['nickname'] . '加油';
//        获取他的帮助记录
        $lotteryHelpLogModel = new LotteryHelpLog();
        $log = $lotteryHelpLogModel->getLogCount($tOpenid);
        $myCount = LotteryHelpLog::where('tOpenid', '=', $tOpenid)->where('fOpenid', '=', $openid)->get();//获取我帮助的次数

//        获取中奖记录
        $lotteryResult = LotteryResult::with('wechatMember')->where('prize_id', '!=', '8')->orderBy('add_time', 'DESC')->take('20')->get();

        return view('game/handlottery/help', compact('tMemberInfo', 'title', 'key', 'log', 'myCount', 'lotteryResult'));
    }

    public function postHelp(Request $request)
    {
        $key = $request->key;
        $result = WechatInfo::where('key', '=', $key)->first()->toArray();
        $k = $result['key'];
        if (!Session::has('openid_' . $k)) {
            return response()->json(['status' => -1, 'msg' => '身份出错，请重试']);
//            return redirect('wechat/auth/'.$k);
        }
        $fOpenid = Session::get('openid_' . $k);
//        $fOpenid = 'oVDTUjlt-L9-2UZZONiLutXPnM2M';
        $tOpenid = $request->openid;
        $add_time = time();
        $data['fOpenId'] = $fOpenid;
        $data['tOpenId'] = $tOpenid;
        $data['add_time'] = $add_time;
        $lotteryHelpLogModel = new LotteryHelpLog();
        $myCount = $lotteryHelpLogModel->getMyLogCount($tOpenid, $fOpenid);//获取我帮助的次数
        if ($myCount >= 1) {
            return response()->json(['status' => -1, 'msg' => '您已经帮助过了']);
        } else {
            LotteryHelpLog::create($data);
        }
        return response()->json(['status' => 0, 'msg' => '您成功的帮助了他，快去通知他吧']);

    }

    function getRand($proArr)
    {
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

    public function postShare(Request $request)
    {
        $openid = $request->openid;
        if (!Cache::has('share_count_' . $openid)) {
            $expiresAt = Carbon::now()->addDay(1);
            Cache::add('share_count_' . $openid, 1, $expiresAt);
        } else {

        }

    }

    public static function getRealIp()
    {
        $ip = "Unknown";
        if (isset($_SERVER["HTTP_X_REAL_IP"]) && !empty($_SERVER["HTTP_X_REAL_IP"])) {
            $ip = $_SERVER["HTTP_X_REAL_IP"];
        } elseif (isset($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]) && !empty($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"])) {
            $ip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]) && !empty($HTTP_SERVER_VARS["HTTP_CLIENT_IP"])) {
            $ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
        } elseif (isset($HTTP_SERVER_VARS["REMOTE_ADDR"]) && !empty($HTTP_SERVER_VARS["REMOTE_ADDR"])) {
            $ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        }
        if ($ip == 'Unknown') {
            // 调试信息
            $ip = '127.0.0.1';
//            self:: debugErrorSendWx('获取不到ip地址', $_SERVER );
        }
        return $ip;
    }


    public function getCard()
    {
        $now = Carbon::now()->hour;

        /*        $total = Cache::get('CardNum_'.$now,function() use($now){
                    $expiresAt = Carbon::now()->addHour(2);
                    Cache::add('CardNum_'.$now,0,$expiresAt);
                    return 0;
                });*/

        $total = WechatCardList::where('status', 1)->where('created_at', '>=', date('Y-m-d H'))->where('cardid', 'pgwjat9rZW0beW15Nvry5k6UFuZY')->count();
        if ($total >= 100) {
            $title = '乌鲁木齐市旅游局';
            $msg = '下手晚了，已经领完啦！亲爱的网友您好，请在当天的9点、11点、13点、15点、17点、19点、21点的7个时间，选择时间点参与拼图游戏，赢取冰雪游体验电子券，看谁手快，小伙伴们拼起来。';
            return view('msg', compact('msg', 'title'));
        }
        if (self::checkTime()) {
//            return redirect('http://app.iyaxin.com/game/pingtu/index.html');
        }
        return redirect('http://mp.weixin.qq.com/s?__biz=MzA4NDUxOTU1NQ==&mid=400714542&idx=1&sn=665ab9ebbd28b2a383d079f832f21038#rd');
    }

    public function postCard($key, Request $request)
    {

        $result ['status'] = 0;
        $wechat_user_info = Session::get('wechat_user_info' . $key);
        $openid = $request->openid;
        if (!$wechat_user_info) {
            $result['msg'] = '身份信息获取失败，请重试';
            return response()->json($result);
        }
        if (!self::checkTime()) {
            $result['msg'] = '还不到时间哦！';
            // return response()->json($result);
        }
        $now = Carbon::now()->hour;

        $total = Cache::get('CardNum_' . $now, function () use ($now) {
            $expiresAt = Carbon::now()->addHour(2);
            Cache::add('CardNum_' . $now, 0, $expiresAt);
            return 0;
        });

        //$total=WechatCardList::where('status',1)->where('created_at','>=',date('Y-m-d H'))->count();

        if ($total >= 18) {
            $result['msg'] = '来晚了一步哦,这个时段的券已经领完了，请下一时段再来哦';
            return response()->json($result);
        }
        $cardId = 'pgwjatxdwr4tcO0X35OItsERZlmE';
        $data = [
            'openid' => $openid,
            'cardid' => $cardId,
            'key' => $key,
        ];
        $result['msg'] = '创建失败';
        $wechatCardInfo = WechatCardList::firstOrNew($data);
        $can = false;
        if ($wechatCardInfo->id) {
            if ($wechatCardInfo->status == 0) {
                //虽然领取过，但未成功领取，可以继续创建
                $can = true;
            } else {
                $result['msg'] = '您已经领取过了';
            }
        } else {
            //第一次创建
            $can = true;
        }

        if ($can) {
            $wechatCardInfo->save();
            $card = new Card('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');
            $cardList = $card->attachExtension($cardId);
            $result['cardList'] = $cardList;
            $result['card'] = true;
            $result['cardid'] = $wechatCardInfo->id;
            $result['status'] = 1;
            $result['msg'] = '正在跳转至卡券';
            //Cache::decrement('CardNum_'.$now);
        }

        return response()->json($result);
    }

    public function postCardchange(Request $request)
    {
        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_info' . $key);
        $openid = $request->openid;
        if (!$wechat_user_info || !($openid == $wechat_user_info['openid'])) {
            $result['msg'] = '非法请求';
            return response()->json($result);
        }

        $wechatCardInfo = WechatCardList::find($request->cardid);
        $wechatCardInfo->status = 1;
        $wechatCardInfo->save();
        $now = Carbon::now()->hour;
        Cache::increment('CardNum_' . $now);
    }

    private function checkTime()
    {
        $time_list = [
            [mktime(12, 0, 0), mktime(22, 0, 0)],

        ];
        $now = Carbon::now()->timestamp;
        $r = false;
        foreach ($time_list as $k => $v) {
            $m = $v[1];
            $l = $v[0];
            $r = $now <= $m && $now >= $l;
            if ($r) {
                break;
            }
        }
        return $r;
    }
} 
