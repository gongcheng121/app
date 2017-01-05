<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/25 0025
 * Time: 下午 6:19
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class WechatOauthApi extends Model{
    protected $table= 'wechat_oauth_api';
    protected $fillable=['code','user_info','state','appid'];

} 