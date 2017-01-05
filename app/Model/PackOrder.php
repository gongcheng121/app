<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/30 0030
 * Time: 下午 5:55
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class PackOrder extends Model{
    protected $table = 'wechat_pack_order';
    protected $fillable = ['mch_billno', 'mch_id','re_openid','amount','status','add_time','key','real_ip','return_code','lucky_id'];
    public function LuckMoneyInfo(){
        return $this->hasOne('App\Model\LuckMoney','id','lucky_id');
    }
} 