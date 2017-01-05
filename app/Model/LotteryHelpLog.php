<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/30 0030
 * Time: 下午 1:54
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class LotteryHelpLog extends Model{
    protected $table = 'wechat_lottery_help_log';
    protected $fillable = ['fOpenId', 'tOpenId','add_time'];

    public function getMyLogCount($tOpenid,$fOpenid){
       return  LotteryHelpLog::where('tOpenid','=',$tOpenid)->where('fOpenid','=',$fOpenid)->count();
    }

    public function getLogCount($openid){
        return LotteryHelpLog::with('wechatMember')->orderBy('id','desc')->where('tOpenid','=',$openid)->get();
    }

    public function getLogCountToday($openid){
        return LotteryHelpLog::with('wechatMember')->where('status','=',0)->where('tOpenid','=',$openid)->where('add_time','>',mktime(0,0,0))->get();
    }

    public function wechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','fOpenId');
    }
} 