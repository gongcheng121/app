<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/11 0011
 * Time: 上午 10:34
 */

namespace App\Http\Controllers;


use App\Commands\SendPacket;
use App\Model\LuckMoney;
use App\Model\PackOrder;
use App\Model\WechatInfo;
use App\Model\WechatMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;

class RedPackController extends BaseController{

    private $qiyelist;
    private $luckyMoneyList;
    private $k;
    private $luckyQiyeInfo;
    private $notTime  = false;
    CONST MAX_NUM = 198;
    CONST START_TIME=10;
    CONST END_TIME = 16;
    public function __construct(){
        parent::__construct();
        $this->middleware('wechat');
        $this->qiyelist =  [
            ['name'=>'乌鲁木齐移动','message'=>'羊汤止沸，猴年生财', 'img'=>asset('qiyeimg/10_0.jpg'),'remark'=>'该红包由晨报与乌鲁木齐移动联合发放'],
            ['name'=>'恒基泰富金融','message'=>'羊汤止沸，猴年生财', 'img'=>asset('qiyeimg/10_1.jpg'),'remark'=>'该红包由晨报与恒基泰富金融控股集团联合发放'],
            ['name'=>'万佳国际家居','message'=>'羊汤止沸，猴年生财', 'img'=>asset('qiyeimg/11_0.jpg'),'remark'=>'该红包由晨报与万佳国际家居联合发放'],
            ['name'=>'乌鲁木齐银行','message'=>'羊汤止沸，猴年生财', 'img'=>asset('qiyeimg/12_0.jpg'),'remark'=>'该红包由晨报与乌鲁木齐银行股份有限公司联合发放'],
            ['name'=>'口腔医院','message'=>'羊汤止沸，猴年生财', 'img'=>asset('qiyeimg/12_1.jpg'),'remark'=>'该红包由晨报与乌鲁木齐市口腔医院联合发放'],
            ['name'=>'新疆阶梯微金投资','message'=>'羊汤止沸，猴年生财。', 'img'=>asset('qiyeimg/13_0.jpg'),'remark'=>'该红包由晨报与新疆阶梯微金投资有限公司联合发放'],
            ['name'=>'五一商场','message'=>'羊汤止沸，猴年生财', 'img'=>asset('qiyeimg/14_0.jpg'),'remark'=>'该红包由晨报与新疆五一商场世纪金马购物中心有限公司联合发放'],
            ['name'=>'西单商场','message'=>'羊汤止沸，猴年生财','img'=>asset('qiyeimg/14_1.jpg'),'remark'=>'该红包由晨报与新疆西单商场百货有限公司联合发放'],
            ['name'=>'宜华家居','message'=>'羊汤止沸，猴年生财','img'=>asset('qiyeimg/15_0.jpg'),'remark'=>'该红包由晨报与宜华家居联合发放'],
            ['name'=>'壹品装饰','message'=>'羊汤止沸，猴年生财','img'=>asset('qiyeimg/15_1.jpg'),'remark'=>'该红包由晨报与壹品装饰工程有限公司乌鲁木齐份公司联合发放'],
            ['name'=>'中粮可口可乐','message'=>'羊汤止沸，猴年生财','img'=>asset('qiyeimg/16_0.jpg'),'remark'=>'该红包由晨报与中粮可口可乐饮料（新疆）有限公司联合发放'],
            ['name'=>'中山百得厨卫','message'=>'羊汤止沸，猴年生财','img'=>asset('qiyeimg/16_1.jpg'),'remark'=>'该红包由晨报与中山百得厨卫有限公司新疆分公司联合发放'],
        ];
        $qiyeList =$this->qiyelist;
        $this->luckyMoneyList = [
            '10_0'=>['qiye'=>$qiyeList[0]],
            '10_1'=>['qiye'=>$qiyeList[1]],
            '11_0'=>['qiye'=>$qiyeList[2]],
            '11_1'=>['qiye'=>$qiyeList[3]],
            '12_0'=>['qiye'=>$qiyeList[4]],
            '12_1'=>['qiye'=>$qiyeList[5]],
            '13_0'=>['qiye'=>$qiyeList[6]],
            '13_1'=>['qiye'=>$qiyeList[7]],
            '14_0'=>['qiye'=>$qiyeList[8]],
            '14_1'=>['qiye'=>$qiyeList[9]],
            '15_0'=>['qiye'=>$qiyeList[10]],
            '15_1'=>['qiye'=>$qiyeList[11]],
        ];


        $h = Carbon::now()->hour;
        $m = (Carbon::now()->minute-30 <0 )? 0 : 1;//时段

        $k = $h."_".$m;
        $this->k = $k;
        if(!isset($this->luckyMoneyList[$k])){
           $this->notTime = true;
        }else{
            $this->luckyQiyeInfo =$this->luckyMoneyList[$k];
            //设置该时段红包总数
            $cache_key = 'Count_'.$k;
            $count = Cache::get($cache_key,function()use($cache_key){
                $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
                Cache::add($cache_key,self::MAX_NUM,$expiresAt);
                return self::MAX_NUM;
            });
        }
    }

    public function getHandle(Request $request){
        $diff =Carbon::now()->between(Carbon::now()->hour(self::START_TIME),Carbon::now()->hour(self::END_TIME));
        if(!$diff || $this->notTime){
            return view('activity/cbredpack/nottime');
        }
        $key = $request->key;
        $wechat_user = Session::get('wechat_user_'.$key);

        return redirect('redpack/index?key='.$key)->with('opencode',Crypt::encrypt($wechat_user['openid']));
    }

    public function getIndex(Request $request){
        $key = $request->key;
        if(!Session::has('opencode')){
            return redirect('http://url.cn/Z0qIs9');
        }
        $code  =  Session::get('opencode');
        $wechat_user = Session::get('wechat_user_'.$key);
        if($wechat_user['openid'] != Crypt::decrypt($code)){
            return redirect('http://url.cn/Z0qIs9');
        }

        $redPackAmount = Cache::get('Count_'. $this->k,function(){return 0;});
        if($redPackAmount==0){
            return redirect('http://app.iyaxin.com/nexttime.html');
        }
//        $wechat_info  = Cache::get('wechat_info_'.$key,function() use($key){
//            try{
//                $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
//                $wechatInfo =WechatInfo::where('key','=',$key)->firstOrFail();
//                Cache::add('wechat_info_'.$key,$wechatInfo,$expiresAt);
//                return  $wechatInfo;
//            }catch (ModelNotFoundException $e){
//                return response('Please contact the Administrator',403);
//            }
//        });
        $openid = $wechat_user['openid'];
        $k = $this->k;
        $qiye = $this->luckyQiyeInfo;
        $cache_key = 'Otc_'.$openid.$k;

        $cache_count = Cache::get($cache_key,function() use($openid,$k,$cache_key){
            $expiresAt = Carbon::now()->addHour(5)->diffInMinutes();
            $count = LuckMoney::where('re_openid',$openid)->where('type',$k)->count();
            Cache::add($cache_key,$count,$expiresAt);
            return $count;
        });

        return view('activity/cbredpack/index',compact('key','qiye','wechat_user','cache_count','redPackAmount'));
    }


    public function postGetPack(Request $request){
        $key  = $request->key;
        $k = $this->k;
        $wechat_user = Session::get('wechat_user_'.$key);
        $openid = $wechat_user['openid'];

        if(Cache::get('Count_'.$k)==0){
            $return =  ['msg'=>'手慢了，该时段的红包已经被抢完了','count'=>0,'status'=>2];
            return response()->json($return);
        }
        $redPackAmount = LuckMoney::where('type',$k)->count();
        if($redPackAmount>=self::MAX_NUM){
            $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
            Cache::put('Count_'.$k,0,$expiresAt);
            $return =  ['msg'=>'手慢了，该时段的红包已经被抢完了','count'=>$redPackAmount,'status'=>2];
            return response()->json($return);
        }

        //判断领取数量
        $cache_key = 'Otc_'.$openid.$k;
        $expiresAt = Carbon::now()->addHour(5)->diffInMinutes();
        $count = LuckMoney::where('re_openid',$openid)->where('type',$k)->count();
        Cache::put($cache_key,$count,$expiresAt);//更新用户红包数量缓存
        $return =  ['msg'=>'','count'=>$count];
        if($count==0){
            Cache::decrement('Count_'.$k,1);//增加缓存
            //只有该段没有领取的才可以操作
            //TODO Step 1 创建红包信息
            $luckyQiye = $this->luckyQiyeInfo;
            $luckyMoneyData = [
                'send_name'=>$luckyQiye['qiye']['name'],
                're_openid'=>$openid,
                'total_amount'=>100,
                'total_num'=>1,
                'wishing'=>$luckyQiye['qiye']['message'],
                'act_name'=>'晨报送红包',
                'remark'=>$luckyQiye['qiye']['remark'],//
                'key' => $key,
                'type'=>$k
            ];
            $luckyMoney = LuckMoney::create($luckyMoneyData);
            //TODO　step 2 生成订单信息
            $order_id = date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 10), 1))), 0, 9);
            $PackOrderData = [
                'mch_billno'=>'88002'.$order_id,
                'mch_id' => '1240688002',
                're_openid' =>$openid,
                'amount' => 100,
                'status' => 0,
                'add_time'  => time(),
                'real_ip' => self::getRealIp(),
                'key' => $key,
                'lucky_id'=>$luckyMoney->id
            ];
            $order= PackOrder::create($PackOrderData);

            $count = LuckMoney::where('re_openid',$openid)->where('type',$k)->count();
            if($count>1){
                //TODO 超出限制次数，则不进行发送红包操作
            }else{
                Cache::increment($cache_key,1);//更新用户红包数量缓存
                Queue::push(new SendPacket($order->id));
            }
            $return =  ['msg'=>'恭喜，您的红包正在飞奔，由于参与人数过多，请耐心等待哦','count'=>$count,'status'=>1];
        }else{
            $return =  ['msg'=>'您该时段已经参与过了，请下一时段继续努力哦','count'=>$count,'status'=>0];
        }
        return response()->json($return);
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