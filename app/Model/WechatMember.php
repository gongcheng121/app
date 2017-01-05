<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 15-7-22
 * Time: 下午5:32
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class WechatMember extends Model {

    protected $table= 'wechat_member';
    public $timestamps = false;
    protected $fillable=['key','openid','nickname','sex','city','province','country','headimgurl'];
    public  function MemberInfo(){
        return $this->hasOne('App\Model\Member','member_id','member_id');
    }
}