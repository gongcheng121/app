<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/2 0002
 * Time: 下午 6:58
 */

namespace app\Http\Controllers;


use App\Model\KanjiaHelpLog;
use App\Model\KanjiaInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;


class WechatKanjiaController extends BaseController {

    public function __construct(){
        $this->middleware('wechat');
        parent::__construct();
    }

    public function getIndex(){
        /*默认页面 屏蔽掉*/
        return ;
    }

    /*
     * 通过微信中给定的链接进行跳转，可判断是否为关注用户参与
     * 条件：1 该链接不可分享到朋友圈
     *      2 设置跳转前的闪存 验证 code 可以是openid
     *      3 跳转页面判断闪存 code 无则非关注
     * */
    public function getHandle($key,Request $request){
//        return '即将进入我的砍价';
        return redirect('/game/kanjia/info')->with('code_key',$key);
    }

    public function getInfo(Request $request,KanjiaInfo $kanjiaInfo,KanjiaHelpLog $kanjiaHelpLog){
        if(!Session::has('code_key')){
            return response('no auth',403);
//            如果没有授权code_key 则是通过非公众号链接
        }
        $key = Session::get('code_key');
        $wechat_user_info = Session::get('wechat_user_'.$key);
        $wechat_user_info['crypt_open_id']= Crypt::encrypt($wechat_user_info['openid']);


        //获取该砍价的详细信息
        $data = [
            'openid'=>$wechat_user_info['openid'],
        ];
        $kanjiaInfoDetail = $kanjiaInfo->firstOrCreate($data);

        //获取是否有自己的砍价记录

        if(!isset($kanjiaInfoDetail->id)){
            //新增  目前只有一个砍价
            $kanjiaInfoDetail->help_count = 0;
            $kanjiaInfo->save();
        }

        $own_log = $kanjiaHelpLog->where('openid',$wechat_user_info['openid'])->where('to_openid',$wechat_user_info['openid'])->where('kid',$kanjiaInfoDetail->id)->count();

        $kanjiaHelpLogModel = $kanjiaHelpLog->where('kid',$kanjiaInfoDetail->id);
        $help_log_list = $kanjiaHelpLogModel->where('to_openid','=',$wechat_user_info['openid'])->where('kid',$kanjiaInfoDetail->id)->with('WechatMember')->get();

        return view('kanjia/info',compact('key','wechat_user_info','kanjiaInfoDetail','own_log','help_log_list'));
    }

    public function getShare($key,Request $request,KanjiaInfo $kanjiaInfo,KanjiaHelpLog $kanjiaHelpLog){
//        显示分享到朋友圈后点开的页面
        $sid = $request->sid;
        $kid = $request->kid;//砍价详情ID
        $to_openid = Crypt::decrypt($sid);//原分享用户OpenId

        $kanjiaInfoModel = $kanjiaInfo->where('id',$kid)->where('openid',$to_openid)->with('WechatMember');
        $kanjiaInfoDetail = $kanjiaInfoModel->first();
        $kanjiaHelpLogModel = $kanjiaHelpLog->where('kid',$kid);
        /*
         * 第一步：判断是否是自己点开的分享页面
         * 如果是自己点开的则跳转会自己的主页
         * 否则显示帮助砍价页面
         * */
        $wechat_user_info = Session::get('wechat_user_'.$key);
        if($wechat_user_info['openid'] == $to_openid){
           //我自己点开的分享链接
            $log_list = $kanjiaHelpLogModel->get()->toArray();//我的帮助列表·
//            return "自己点开的";
            return redirect('game/kanjia/handle/'.$key);
        }
        //获取我帮助他的记录
//        $log  = $kanjiaHelpLogModel->where('openid',$wechat_user_info['openid'])->where('to_openid','=',$to_openid)->count();
        $help_log_list = $kanjiaHelpLogModel->where('to_openid','=',$to_openid)->where('kid',$kid)->with('WechatMember')->get();
        return view('kanjia/help',compact('kanjiaInfoDetail','key','to_openid','wechat_user_info','help_log_list','sid'));
    }


    /*
     * 投票，只能投票一次
     * */
    public function postHelp(Request $request,KanjiaHelpLog $kanjiaHelpLog,KanjiaInfo $kanjiaInfo){
        $data = ['status'=>0,'msg'=>''];
        $kid = $request->input('kid');
        $openid = $request->input('openid');
        $to_openid = $request->input('to_openid');
        $data=['kid'=>$kid,'openid'=>$openid,'to_openid'=>$to_openid,'money'=>300];
        $kanjiaHelpLogs = $kanjiaHelpLog->firstOrNew($data);
        if($kanjiaHelpLogs->id){
            $data=['status'=>'-1','msg'=>'数据已经存在，请勿重复提交'];
        }else{
            $kanjiaHelpLogs->save();
            $kanjiaInfo->where('id',$kid)->increment('help_count');
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



}