<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 19/01/16
 * Time: 下午 12:35
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class WechatMemberDetail extends Model
{
    protected $table= 'wechat_member_detail';
    protected $fillable=['openid','true_name','id_card','mobile','tel','address'];

    public function Code(){
        return $this->hasMany('App\Model\WechatCardList','openid','openid');
    }
}