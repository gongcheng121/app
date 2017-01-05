<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/30 0030
 * Time: 下午 5:55
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class WechatKeyVerify extends Model{
    protected $table = 'wechat_verify_key';
    protected $fillable = ['id', 'password','key','openid','status'];

    public function Member(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
} 