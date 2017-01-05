<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 15-7-22
 * Time: 上午11:42
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class WechatUser extends Model {

    protected $table = 'wechat_user';
    protected $fillable = ['id','openid'];


}