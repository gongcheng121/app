<?php
use App\Model\WechatInfo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

/**
 * Created by PhpStorm.
 * User: koala
 * Date: 2016/6/12
 * Time: 17:01
 */



function is_mobile() {
    static $is_mobile = null;

    if ( isset( $is_mobile ) ) {
        return $is_mobile;
    }

    if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
        $is_mobile = false;
    } elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}


function getOpenId($key){
    return Session::get('wechat_user_'.$key);
}

//插入一段字符串
function str_insert($str, $i, $substr)
{
    if(!$str) return '';
    $startstr='';
    $laststr='';
    for($j=0; $j<$i; $j++){
        $startstr .= $str[$j];
    }
    for ($j=$i; $j<strlen($str); $j++){
        $laststr .= $str[$j];
    }
    $str = ($startstr . $substr . $laststr);
    return $str;
}
function asset_thumb($path){
    return app('url')->asset($path);
}

function getRand($proArr)
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

function getWecahtInfo($key){
    $wechat_info  = Cache::get('wechat_info_'.$key,function() use($key){
        try{
            $expiresAt = Carbon::now()->addDay(1);
            $wechatInfo =WechatInfo::where('key','=',$key)->firstOrFail();
            Cache::add('wechat_info_'.$key,$wechatInfo,$expiresAt);
            return  $wechatInfo;
        }catch (ModelNotFoundException $e){
            return response('Please contact the Administrator',403);
        }
    });
    return $wechat_info;
}