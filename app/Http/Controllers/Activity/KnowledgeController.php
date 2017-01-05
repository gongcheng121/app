<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2016/3/16
 * Time: 17:18
 */

namespace App\Http\Controllers\Activity;


use App\Commands\SendWechatMessage;
use App\Http\Controllers\BaseController;
use App\Model\Knowledge;
use App\Model\WechatMemberDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Overtrue\Wechat\Staff;

class KnowledgeController extends BaseController {

    private $answer=[
        1=>2,
        2=>2,
        3=>2,
        4=>2,
        5=>3,
        6=>1,
        7=>1,
        8=>10,
        9=>10,
        10=>15,
        11=>10,
    ];

    function __construct()
    {
        parent::__construct();
        $this->middleware('wechat');
    }


    public function getIndex(Request $request){

//        $appId  = 'wx2a806911636ec3d7';
//        $secret = '621304bc7c0047d9ae43b81f9224d00e';
//        $userService = new Staff($appId, $secret);

        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_'.$key);
//        Queue::push(new SendWechatMessage(['message'=>'感谢您的支持，欢迎您4月10日来活动现场（铁路局汇嘉时代一楼）领取参与奖一份，还可参加现场其他活动，奖品多多哦。','openid'=>$wechat_user_info['openid']]));
//        $userService->send('您参加了保险知识问答，test 获得了,凭借此消息即可到指定地点领取奖品')->to($wechat_user_info['openid']);

        return view('activity/knowledge/index',compact('key','wechat_user_info'));
    }


    public function postAnswer(Request $request,Knowledge $knowledge){
        $score = 0;
        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_'.$key);
        $result  = $knowledge->where('openid',$wechat_user_info['openid'])->get()->toArray();
        if(sizeof($result)>0){
            $return  = ["recode"=>202,"reason"=>"已提交过答案，不可修改","data"=>json_decode($request->answer),"info"=>['fen'=>$result[0]['fen']]];
            return response()->json($return);
        }

        $answer = $request->get('answer',false);
        $data['answer']=$answer;
        $data['openid']=$wechat_user_info['openid'];
        $data['key']=$key;
        $data['status']=1;
        if(!$answer){
            $return  = ["recode"=>201,"reason"=>"请选择正确的答案格式","data"=>json_decode($request->answer)];
            return response()->json($return);
        };
        $answer=json_decode($answer);
        foreach($answer as $key=>$val){
            $count =0;
            $count = array_reduce($val,function($v,$w){
                $v+=$w;
                return $v;
            });
            if($this->answer[$key]==$count){
                $score++;
            }
        }
        $counts = $score;
        $score = ($score/sizeof($this->answer))*100;
        $fen = round($score,2);
        $data['fen']=$fen;
        $knowledge->firstOrCreate($data);

        if($fen<8/11){
            Queue::push(new SendWechatMessage(['message'=>'感谢您的支持，欢迎您4月10日来活动现场（铁路局汇嘉时代一楼）领取参与奖一份，还可参加现场其他活动，奖品多多哦。','openid'=>$wechat_user_info['openid']]));
        }else{
            Queue::push(new SendWechatMessage(['message'=>'恭喜您已答对'.$counts.'道题，欢迎您4月10日来活动现场（铁路局汇嘉时代一楼）领取精美礼品一份，还可参加现场其他活动，奖品多多哦。','openid'=>$wechat_user_info['openid']]));
        }

        $return  = ["recode"=>200,"reason"=>"","data"=>json_decode($request->answer),"info"=>['fen'=>$fen]];
        return response()->json($return);
    }

    public function postDetail(Request $request){

        $key = $request->key;
        $wechat_user_info = Session::get('wechat_user_'.$key);


        $datas['openid'] = $wechat_user_info['openid'];
        $data['true_name'] = $request->get('name', false);
        $data['mobile'] = $request->get('mobile', false);
        if ($data['true_name'] == false || $datas['openid'] == false || $data['mobile'] == false) {
            return response()->json(['error' => 1, 'msg' => '请填写完整']);
        }
        $count = WechatMemberDetail::where('openid', $datas['openid'])->count();
        if ($count >= 1) {
            return response()->json(['error' => 1, 'msg' => '账号已被绑定']);
        }
        $detail = WechatMemberDetail::firstOrCreate($datas);
        $detail->true_name=$data['true_name'];
        $detail->mobile= $data['mobile'];
        $detail->save();
        return 1;
    }
}