<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2016/5/20
 * Time: 10:21
 */

namespace App\Http\Controllers\Activity;


use App\Http\Controllers\BaseController;
use App\Model\GameHelpInfo;
use App\Model\GameHelpLog;
use App\Model\LotteryCount;
use App\Model\LotteryResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class EmeiController extends BaseController
{

    function __construct()
    {
        parent::__construct();
        $this->middleware('wechat');
        Carbon::setLocale('zh');
    }


    public function getIndex(Request $request, GameHelpInfo $gameHelpInfo, GameHelpLog $gameHelpLog, LotteryResult $lotteryResult)
    {
        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_' . $key);
        $data = [
            'openid' => $wechat_user_info['openid'],
        ];

        $game_count = $gameHelpInfo->count();
        $gameHelpInfoDetail = $gameHelpInfo->firstOrCreate($data);
        $prize_log = [];
        if (!isset($gameHelpInfoDetail->id)) {
            //新增  目前只有一个帮助
            $gameHelpInfoDetail->help_count = 0;
            $gameHelpInfoDetail->save();
        } else {
            $prize_log = $lotteryResult->where('openid', $wechat_user_info['openid'])->get();
        }
        $help_log_list = Cache::get('help_log' . $gameHelpInfoDetail->id, function () use ($gameHelpInfoDetail, $gameHelpLog, $wechat_user_info) {
            $help_log_list = $gameHelpLog->where('kid', $gameHelpInfoDetail->id)->where('to_openid', '=', $wechat_user_info['openid'])->where('kid', $gameHelpInfoDetail->id)->with('WechatMember')->orderBy('id', 'DESC')->get();

            Cache::put('help_log' . $gameHelpInfoDetail->id, $help_log_list, Carbon::now()->addMinute(1)->diffInMinutes());
            return $help_log_list;
        });

        $lotteryResult = Cache::get('lottery_result_', function () {
            $result = LotteryResult::with('wechatMember')->orderBy('key','desc')->where('prize_id', '!=', '32')->get();
            Cache::put('lottery_result_',$result,Carbon::now()->addMinute(20));
        });
        $lotter_info = Cache::get('Lotter_info' . $wechat_user_info['openid'], function () {
            return [];
        });
        return view('activity/emei/index', compact('key', 'gameHelpInfoDetail', 'help_log_list', 'wechat_user_info', 'prize_log', 'lotter_info','lotteryResult','game_count'));
    }


    public function getShare(Request $request, GameHelpInfo $gameHelpInfo, GameHelpLog $gameHelpLog)
    {
        $key = $request->key;
        if (!$request->fromopenid) {
            return ('403 frobitten');
        }

        $wechat_user_info = Session::get('wechat_user_' . $key);
        if ($wechat_user_info['openid'] == $request->fromopenid) {
//            自己的页面 需要跳转回去
            return redirect('emei/index?key=' . $key);
        }


//        Step 1 获取我帮助的记录
        $log = $gameHelpLog->where('openid', $wechat_user_info['openid'])->where('to_openid', '=', $request->fromopenid)->count();
        $help_log_list = $gameHelpLog->where('to_openid', '=', $request->fromopenid)->where('kid', $request->kid)->with('WechatMember')->orderBy('id', 'DESC')->get();
        $to_openid = $request->fromopenid;
        $to_kid = $request->kid;
        return view('activity/emei/share', compact('key', 'log', 'help_log_list', 'wechat_user_info', 'to_openid', 'to_kid'));
    }

    public function postHelp(Request $request, GameHelpInfo $gameHelpInfo, GameHelpLog $gameHelpLog)
    {
        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_' . $key);
        $data = ['status' => 0, 'msg' => ''];
        $kid = $request->input('to_kid');
        $openid = $wechat_user_info['openid'];
        $to_openid = $request->input('to_openid');
        $data = ['kid' => $kid, 'openid' => $openid, 'to_openid' => $to_openid];
        $gameHelpLogs = $gameHelpLog->firstOrNew($data);
        if ($gameHelpLogs->id) {
            $data = ['status' => '-1', 'msg' => '数据已经存在，请勿重复提交'];
        } else {
            $gameHelpLogs->save();
            $gameHelpInfo->where('id', $kid)->increment('help_count');
            $data = ['status' => 1, 'msg' => 'success'];
            return response()->json($data);
        }
        return response()->json($data);
    }

    public function postLottery(Request $request, LotteryResult $lotteryResult)
    {
        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_' . $key);

        $lotter_info = Cache::get('Lotter_info' . $wechat_user_info['openid'], function () {
            return [];
        });
        $prize_count = $lotteryResult->where('openid', $wechat_user_info['openid'])->where('prize_id', '!=', '32')->count();


//        dd($prize_count);

        if (sizeof($lotter_info) >= 2) {
            $return['error'] = 1;
            $return['count'] = 0;
            $return['msg'] = '超出游戏次数';
        } else {
            $prize_list = [
                '1' => '爱心猴',
                '2' => '新春猴',
                '3' => '毛峰',
                '4' => '音乐猴',
                '5' => '悠嘻猴',
                '6' => '峨眉山雪芽',
                '0' => '谢谢参与'
            ];

            $prize_arr = array(
                '32' => array('id' => 32, 'min' => [166, 308], 'max' => [198, 336], 'prize' => $prize_list[0], 'v' => 300, 'count' => 100000000000),
                '35' => array('id' => 35, 'min' => '0', 'max' => '15','prize' => $prize_list[1], 'v' => 40, 'count' => 40),//爱心猴
                '36' => array('id' => 36, 'min' => '20', 'max' => '50', 'prize' => $prize_list[2], 'v' => 90, 'count' => 90),//新春猴
                '37' => array('id' => 37, 'min' => '57', 'max' => '87', 'prize' => $prize_list[3], 'v' => 0, 'count' => 0),//毛峰
                '38' => array('id' => 38, 'min' => '134', 'max' => '162', 'prize' => $prize_list[4], 'v' => 100, 'count' => 100),//音乐猴
                '39' => array('id' => 39, 'min' => '205', 'max' => '230', 'prize' => $prize_list[5], 'v' => 2, 'count' => 2),//悠嘻猴
//                '40' => array('id' => 40, 'prize' => $prize_list[6], 'v' => 20, 'count' => 10),
            );
            if ($prize_count >= 1) {
                $prize_arr = array(
                    '32' => array('id' => 32, 'min' => [166, 308], 'max' => [198, 336], 'prize' => $prize_list[0], 'v' => 1000, 'count' => 100000000000),
                );
            }
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
            $rid = $this->getRand($arr);
            $res = $prize_arr[$rid]; //中奖项
            $time = time();
            LotteryResult::create(['openid' => $wechat_user_info['openid'], 'lottery' => $res['prize'], 'add_time' => $time, 'prize_id' => $res['id']]);
            $Lottery_count = LotteryCount::firstOrCreate(['prizeId' => $res['id'], 'prize' => $res['prize']]);
            $Lottery_count->increment('count', 1);
            $lotter_info['prize'][] = $res['prize'];

            $min = $res['min'];
            $max = $res['max'];
            if ($rid != 32) {
                $return['angle'] = mt_rand($min, $max);
                $return['msg'] = '恭喜您获得' . $res['prize'] . '别气馁哦，转发5个好友帮助你提高中奖几率哦';
            } else {

                $i = mt_rand(0, 1);

                $return['angle'] = mt_rand($min[$i], $max[$i]);
                $return['msg'] = $res['prize'] . '别气馁哦，转发5个好友帮助你提高中奖几率哦';
            }
            $return['prize'] = $res['prize'];
            Cache::put('Lotter_info' . $wechat_user_info['openid'], $lotter_info, Carbon::now()->addDay(10)->diffInMinutes());
        }

        return response()->json($return);
    }

    private function getRand($proArr)
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
}