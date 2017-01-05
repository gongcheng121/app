<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/29 0029
 * Time: 下午 5:58
 */

namespace App\Http\Controllers;


use App\Model\Crawer;
use App\Model\CrawerLinks;
use App\Model\CrawerPos;
use App\Repositories\BabyVoteRepositoryEloquent;
use Carbon\Carbon;
use EasyWeChat\Core\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Ixudra\Curl\Facades\Curl;

//use Overtrue\Wechat\Http;
//use Overtrue\Wechat\LuckMoney;
//use Overtrue\Wechat\Payment\Business;
//use Overtrue\Wechat\RedPack;
class ApiController extends Controller
{

    public function postIndex(Request $request)
    {
        $appid = 'wx36bb5c554898cc1a';
        $secret = '8949a67cf7fa442d7e3f2fb4efcdcf85';
        $mch_id = '1240688002';
        $pay_key = 'xvPtzCDaIX23mqaNfEulqs3Zs3U3GlII';
        $business = new Business($appid, $secret, $mch_id, $pay_key);

        $apiclient_cert = "/data/www/app/cert/chengbao/apiclient_cert.pem";
        $apiclient_key = "/data/www/app/cert/chengbao/apiclient_key.pem";
        $business->setClientCert($apiclient_cert);
        $business->setClientKey($apiclient_key);

        $luckMoneyData = $request->all();
        $luckMoneyServer = new LuckMoney($business);
        $result = $luckMoneyServer->send($luckMoneyData);
        return $result;
    }

    public function anyWeather(Request $request)
    {
        $this->middleware('api');
        if (!str_contains($request->server('HTTP_REFERER'), 'iyaxin.com')) {
//            return response('404');
        }
        $result = Cache::get('weather_result', function () {
            $result = Curl::to('http://m.iyaxin.com/tool/weather')->get();
            $result = json_decode($result);
            $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
            Cache::add('weather_result', $result, $expiresAt);
            return $result;
        });

        return response()->json($result)->setCallback($request->callback);
    }


    public function anyBaby(BabyVoteRepositoryEloquent $repository, Request $request)
    {
        $result = $repository->with('poll')->all();
        return response()->json(['code' => 1, 'data' => $result])->setCallback($request->callback);
    }

    public function postBabyPoll()
    {

    }

    public function postLive()
    {
        $result['time'] = Carbon::today()->format('Y-m-d H:i:s ');
        $result['live'] = Crawer::where('status', 1)->count();
        $result['rank']=DB::connection('connection-sails')->table('crawerlinks')->select(DB::raw('count(*) as count'),'title','link')->where('createdAt','>',$result['time'])->groupBy('title')->limit(15)->orderBy('count','desc')->get();

        return $result;
    }

    public function postPos(){
        $time = Carbon::now()->addHour(-1)->format('Y-m-d H:i:s ');
        $result['pos']=CrawerPos::select('pos_x','pos_y','sid')->limit(5000)->where('createdAt','>',$time)->orderBy('id','desc')->get();
        return $result;
    }

}