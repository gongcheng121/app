<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 15-7-22
 * Time: 下午1:39
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class LotteryResult extends Model{
    protected $table = 'wechat_lottery_result';
    protected $primaryKey = 'key';
    protected $fillable = ['openid', 'lottery','add_time','prize_id','status'];

    public function wechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
}