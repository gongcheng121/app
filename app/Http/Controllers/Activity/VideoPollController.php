<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 02/02/16
 * Time: 下午 02:49
 */

namespace App\Http\Controllers\Activity;


use App\Commands\Card;
use App\Http\Controllers\BaseController;
use App\Model\LotteryCount;
use App\Model\LotteryResult;
use App\Model\VideoPoll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Overtrue\Wechat\Exception;
use Overtrue\Wechat\Http;

class VideoPollController extends BaseController
{
    const MAX_TIME=1;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('wechat');
    }

    public function getIndex(VideoPoll $videoPoll,Request $request){
        $videoList = $videoPoll->orderBy('id','DESC')->orderBy('listorder','DESC')->paginate(155);
        $key = $request->key;

        return view('activity/video/index',compact('videoList','key'));
    }

    public function anyVideoApi(Request $request,Http $http){
        $url = $request->url;
        $k=md5($url);
        $src  = Cache::get($k,function()use($k,$http,$url){
            $expiresAt = Carbon::now()->addMinute(30)->diffInMinutes();
            try{
                $src = $http->get('http://220.171.90.234:9033/api/qqvideo?u='.$url);
                Cache::put($k,$src,$expiresAt);
                return $src;
            }catch (Exception $e){
                 return '';
            }
        });
        return $src;
    }


    public function postPoll(Request $request,VideoPoll $videoPoll){
        $id =$request->id;
        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_'.$key);
        $openid = $wechat_user_info['openid'];
        $key = 'videopoll'.$openid.$id;
        $has = Cache::get($key,function()use($key){
            return false;
        });
        if($has){
            $return['error']='1';
            $return['data']='您已经投过票，请明天再来';
        }else{
            $return = ['error'=>'','data'=>'投票成功'];
            $video = $videoPoll->find($id);
            $video->increment('count');
            $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
            Cache::put($key,true,$expiresAt);
        }
        $lotter_info = Cache::get('Lotter_info'.$openid,function() use($openid){
            Cache::put('Lotter_info'.$openid,['count'=>0],Carbon::now()->addDay(100)->diffInMinutes());
            return ['count'=>0];
        });

        return response()->json($return);
    }

    public function postPrize(Request $request,VideoPoll $videoPoll){
        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_'.$key);
        $openid = $wechat_user_info['openid'];
        $return = ['error'=>0,'msg'=>''];
        $lotter_info = Cache::get('Lotter_info'.$openid);
        $return['count']=self::MAX_TIME-$lotter_info['count'];
        if(self::MAX_TIME-$lotter_info['count']>0){
            $lotter_info['count']+=1;
            $prize_list  = [
                '0'=>'谢谢参与',
                '1'=>'恭喜您获得 神州专车新年礼券',
                '2'=>'恭喜您获得 电子滑雪卡',
            ];
            $prize_arr = array(
                '11' => array('id'=>11,'prize'=>$prize_list[1],'v'=>30,     'count'=>5000),
                '12' => array('id'=>12,'prize'=>$prize_list[2],'v'=>30,     'count'=>250),
                '19' => array('id'=>19,'prize'=>$prize_list[0],'v'=>100,   'count'=>10000000),
            );
            if(isset($lotter_info['prize'])){
                $prize_arr = array(
                    '19' => array('id'=>19,'prize'=>$prize_list[0],'v'=>1000,   'count'=>10000000),
                );
            }
            $count = LotteryCount::all()->toArray();
            foreach ($prize_arr as $key => $val) {
                $arr[$val['id']] = $val['v'];
                $c[$val['id']] = $val['count'];
            }
            if(sizeof($count)>1){
                foreach($count as $k=>$v){
                    if(isset($c[$v['prizeId']])) {
                        $p = $c[$v['prizeId']]-$v['count'];
                        if($p<=0){
                            unset($arr[$v['prizeId']]);
                        }
                    }
                }
            }

            $rid = $this->getRand($arr);

            $res = $prize_arr[$rid]; //中奖项
            $time =time();
            LotteryResult::create(['openid'=>$openid,'lottery'=>$res['prize'],'add_time'=>$time,'prize_id'=>$res['id']]);
            $Lottery_count = LotteryCount::firstOrCreate(['prizeId'=>$res['id'],'prize'=>$res['prize']]);
            $Lottery_count->increment('count',1);
            if($rid!=19){
                $lotter_info['prize']=$res['prize'];
                $return['msg']='恭喜您获得'.$res['prize'];
                if($rid ==12){
                    $card = new Card('wx2d970cc5a44a0597', '54d1be2aa3f25b992c41c28c67343420');
                    $cardList = $card->attachExtension('pgwjat0KPZm7dv6KQkQKzsXFfS1E');
                    $return['cardList'] = $cardList;
                }
                if($rid==11){
                    $return['links']='http://mktm.10101111.com/html5/2015/onethd/index.html?onethdFrom=danlu';
                }
            }
            $return['msg']=$res['prize'];
            $return['prize']=$res['prize'];
            Cache::put('Lotter_info'.$openid,$lotter_info,Carbon::now()->addDay(1)->diffInMinutes());
        }else{
            $return['error']=1;
            $return['count']=self::MAX_TIME-$lotter_info['count'];
            $return['msg']='超出游戏次数';
        }
        return response()->json($return);
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
}