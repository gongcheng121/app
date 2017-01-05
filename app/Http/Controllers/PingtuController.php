<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/3 0003
 * Time: 上午 11:46
 */

namespace app\Http\Controllers;


use App\Model\Pingtu;
use Illuminate\Http\Request;

class PingtuController extends BaseController{


    public function getIndex(Request $request){

    }

    public function postLottery(Request $request){

        $openid = $request->openid;
        $PintuModel = Pingtu::where('openid',$openid);

        dd($request->all());
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