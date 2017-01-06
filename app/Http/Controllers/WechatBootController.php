<?php

namespace App\Http\Controllers;

use App\Extensions\Wechat\WebApi;
use App\Jobs\SendWechatMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Ixudra\Curl\Facades\Curl;

class WechatBootController extends Controller
{
    //
    public function __construct()
    {
    }

    public function getIndex()
    {



/*        $this->api = WebApi::restoreState();
        $user = $this->api->getLoginUser();


        $contact = $this->api->getContact();
        $to = $contact->getUserByNick('哎呀呀。')['UserName'];
//        $to = $contact->getUserByNick('苦逼九人组')['UserName'];
        dd($to);
        $job = (new SendWechatMessage('@3b4a787accd7abbaaf5448416dd076e0f85fd794f7428e55024c66d81e65c0d2',Carbon::now().' time'))
            ->onConnection(env('QUEUE_DRIVER', 'database'))
            ->onQueue(env('JOB_QUEUE', 'default'));
        dispatch($job);
        dd('ok');

//        $to = $contact->getUserByNick('苦逼九人组')['UserName'];
        $this->api->sendMessage('@@8cfa419581521de0dc96e15261f159e305a4c2cd6ffd42a0edd31824aee78015', '现在时间是'.Carbon::now());

        $can_arr = [
            '@df0dba46f89ca671a19eabec060771898f75aab0198cdea78337becf4929f77a',
            '@@80e3d3056da04320cba6ddc58cc2e78e6347346af3638defba4e3f8512f9f434',
            '@@8cfa419581521de0dc96e15261f159e305a4c2cd6ffd42a0edd31824aee78015'
        ];

        dd(in_array('@@8cfa419581521de0dc96e15261f159e305a4c2cd6ffd42a0edd31824aee78015',$can_arr));
        return response()->json($contact->toArray());*/
        $stepOneUrl = 'https://login.wx.qq.com/jslogin?appid=wx782c26e4c19acffb&redirect_uri=https%3A%2F%2Fwx.qq.com%2Fcgi-bin%2Fmmwebwx-bin%2Fwebwxnewloginpage&fun=new&lang=zh_CN&_=1483607230848';

        $stepOneResponse = Curl::to($stepOneUrl)->get();
        preg_match('/"(.*?)"/', $stepOneResponse, $match);
        $uuid = $match[1];
        session(['uuid' => $uuid]);

        return view('wechatBoot.index', compact('uuid'));
    }

    public function postStatus(Request $request)
    {
        $uuid = $request->input('uuid');
        $url = sprintf("https://login.wx2.qq.com/cgi-bin/mmwebwx-bin/login?uuid=%s&tip=1&_=%s", $uuid, getMillisecond());
        $response = Curl::to($url)->get();
        preg_match('/=(.*?);/', $response, $match);

        $result = $match[1];
        $data['status'] = 0;
        if ($result == 200) {
            //登陆成功
            preg_match('/redirect_uri="(.*?)";/', $response, $match2);
            $result = $match2[1];
        }

        if ($result == 201) {
            $data['status'] = 1;
        } elseif (substr_count($result, 'http')) {
            //确认成功
            $data = array('status' => 2);
        }
        $data['msg'] = $result;

        return response()->json($data);
    }


    public function postCookies(Request $request)
    {

        $url = $request->input('url');
        $response = Curl::to($url)->withOption('COOKIEJAR', storage_path('cookie\\' . session('uuid') . 'cookie'))->post();
        $load = (array)simplexml_load_string($response);


        if ($load['ret'] == '1203') {
            return '1203';
        }
        $wxuin = $load['wxuin'];
        $wxsid = $load['wxsid'];
        $skey = $load['skey'];
        $pass_ticket = $load['pass_ticket'];

        session(['wxuin'=> $wxuin]);
        session(['wxsid'=>$wxsid]);
        session(['skey'=>$skey]);
        session(['pass_ticket'=>$pass_ticket]);
        $return = [
            'status' => 1,
            'uin' => $wxuin,
            'sid' => $wxsid
        ];

        return response()->json($return);

    }

    /*
     * 登录成功，初始化微信信息
     */
    public function postInit(Request $request)
    {

        $url = "https://wx8.qq.com/cgi-bin/mmwebwx-bin/webwxinit?r=". ~getMillisecond()."&pass_ticket=".session('pass_ticket');

        $data['BaseRequest']=[
            'Uin'=>session('wxuin'),
            'Sid'=>session('wxsid'),
            'Skey'=>session('skey'),
            'DeviceID'=>'e' . rand(10000000, 99999999) . rand(1000000, 9999999)
        ];

        $response = Curl::to($url)->withData(($data))
            ->asJsonRequest()
            ->withHeader('Origin',"https://wx8.qq.com")
            ->withHeader('User-Agent',"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36")
            ->withHeader('Referer',"https://wx8.qq.com/?&lang=en")
            ->withContentType('application/json;charset=UTF-8')
            ->asJson(true )
            ->withOption('REFERER','https://wx8.qq.com/?&lang=zh_CN')->post()
        ;

        return ($response);
    }
}
