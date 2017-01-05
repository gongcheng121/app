<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 21/01/16
 * Time: 下午 07:11
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\BaseController;
use App\Model\WechatCardList;
use App\Model\WechatMemberDetail;
use Illuminate\Http\Request;

class WechatMemberVerify extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getIndex(){
        return view('api/index');
    }

    public function postIndex(Request $request,WechatMemberDetail $wechatMemberDetail,WechatCardList $wechatCardList){
        $idcard  = $request->get('idcard',false);
//        $code = $request->get('code',false);
        $wechatMemberModel = $wechatMemberDetail->with(['Code'=>function($q){
            $q->where('cardid','pgwjat1TukOTEHDidoeWuxkaDkNM');
        }]);

        if($idcard){
            $detail = $wechatMemberModel->where('id_card',$idcard)->first();
            if($detail){
                $details = $detail->toArray();
                return view('api/result',compact('details'));
            }
        }
    }

}