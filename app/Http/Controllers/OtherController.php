<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2015/5/26
 * Time: 11:49
 */

namespace App\Http\Controllers;


use App\Model\Shucai;
use App\Model\Jiasu;
use App\Model\Taozi;
use App\Model\VideoInfo;
use App\Model\WechatMember;
use App\Model\WifiArea;
use App\Model\Zhuoji;
use App\Model\ZhuojiCode;
use App\Services\Helper;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class OtherController extends BaseController{
    public function __construct(){
//        parent::__construct();
    }

    public function getSend(){
        $key  ='7cb551a19e58ed5524f2be99f251c405';


        $wechat_info = Cache::get('wechat_info_' . $key, function () use ($key) {
            try {
                $expiresAt = Carbon::now()->addDay(1)->diffInMinutes();
                $wechatInfo = WechatInfo::where('key', '=', $key)->firstOrFail();
                Cache::add('wechat_info_' . $key, $wechatInfo, $expiresAt);
                return $wechatInfo;
            } catch (ModelNotFoundException $e) {
                return response('Please contact the Administrator', 403);
            }
        });

        $appId = $wechat_info['appid'];
        $secret = $wechat_info['secret'];
        $config = [
            'app_id' => $appId,
            'secret' => $secret,

        ];

        $app = new Application($config);
        $userService = $app->user;

        $openId  =  'oVDTUjrvP8-TkemcYJ1mxPyAsvUo';
        $member = $userService->get($openId);
        dd($member);



    }
    public function getDaily(){
        $title='三块地毯的故事';
        $share=asset('images/one.png');
        $src= 'http://xjdaily.kuaizhan.com/fp/page/display/556c856c47034567340971de';
        $key = '7cb551a19e58ed5524f2be99f251c405';
        $description='2015年5月5日，吐鲁番农民古丽阿衣先木夫妇给张春贤书记送来去年10月预定的三块地毯。';
        return view('daily.index',compact('title','share','src','key','description'));
    }
    public function getOne(){
        $title='我们都是一家人';
        $share=asset('images/one.png');
        $src= 'http://eqxiu.com/s/SKGlom1p';
        $key = '7cb551a19e58ed5524f2be99f251c405';
        $description='2015年5月5日，吐鲁番农民古丽阿衣先木夫妇给张春贤书记送来去年10月预定的三块地毯';

        return view('other.one',compact('title','share','src','key','description'));
    }
    public function getChildren(){
        return view('other.children');
    }

    public function getWifiarea(){
        return view('other.wifi');
    }
    public function postWifiarea(){
        $result = WifiArea::get();
        return response()->json($result);
    }

    public function getVideo(){
        return view('other.video');
    }
    public function postVideo(Request $request){
        $data['name']= $request->name;
        $data['beizhu'] = $request->beizhu;
        $data['file'] = $request->file;
        $data['mobile'] = $request->mobile;
        $result = VideoInfo::create($data);
        return '上传成功，谢谢参与';
    }
    public function postVideofile(Request $request){
        $path = 'upload/video/'.date('ymd').'/';
        $type = $request->file('file')->guessExtension();
        $name = str_random(8).".".$type;
        $result = $request->file('file')->move($path,$name);
        return response()->json(['file'=>$path.$name]);
    }

    public function getDuanwu(){
        return view('other.duanwu');
    }

//蔬菜游戏排名
    public function getShucairank(){
        $shucais = Shucai::orderBy('score','desc')->orderBy('id','desc')->paginate(15);

        return view('other.shucai',compact('shucais'));
    }
//桃子游戏排名
    public function getTaozirank(){
        $shucais = Taozi::orderBy('score','desc')->orderBy('id','desc')->paginate(15);
        return view('other.taozi',compact('shucais'));
    }
//    记录蔬菜游戏分数
    public function postShucaiscore(Request $request){
        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['score'] = $request->score;
        $data['address'] = $request->address;

        $shucai=  Shucai::firstOrCreate(['mobile'=>$data['mobile']]);
        $shucai->update($data);
        $shucai->save();
        $return = ['status'=>["code"=>'10','message'=>'提交成功'],'data'=>['id'=>$shucai->id]];
        return response()->json($return);
    }

    public function postZhuojiscore(Request $request){
        $key = $request->key;
        $data['name']=$request->get('name','');
        $data['mobile']=$request->get('mobile','');
        $data['score']=$request->get('score','');
        $data['openid']=$request->get('openid','');
        $zhuoji=  Zhuoji::firstOrCreate(['openid'=>$data['openid']]);
        $zhuoji->update($data);
        $zhuoji->save();

        if($data['score']>=88 && !$zhuoji->code){
            $codes = ZhuojiCode::where('status',0)->first();
            if($codes){
                $zhuoji->code = $codes->code.','.$codes->pwd;
                $zhuoji->save();
                $codes->status=1;
                $codes->save();
            }
        }
        $return = ['status'=>["code"=>'10','message'=>'提交成功'],'data'=>['id'=>$zhuoji->id]];
        return response()->json($return);
    }

    public function getZhuojiscore(Request $request){

        $key = $request->key;
        $member= getOpenId($key);

        if(!$member)return;
        $openid=  $member['openid'];

        $zhuoji = Zhuoji::where('openid',$openid)->first();
        if($zhuoji->code){
            list($data['code'],$data['pwd']) = explode(',',$zhuoji->code);
        }else{
            $data['error']=1;
        }

        return response()->json($data);
    }
    //    记录蔬菜游戏分数
    public function postTaoziscore(Request $request){
        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['score'] = $request->score;

        $shucai=  Taozi::firstOrCreate(['mobile'=>$data['mobile']]);
        $shucai->update($data);
        $shucai->save();
        $return = ['status'=>["code"=>'10','message'=>'提交成功'],'data'=>['id'=>$shucai->id]];
        return response()->json($return);
    }

    public function anyJiasudata(Request $request,Jiasu $jiasu){
        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['content'] = $request->score;

       $jiasu->create($request->all());
        return response()->json(['success'=>1,'data'=>$request->all()]);
    }

} 