<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/22 0022
 * Time: 上午 11:08
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class WechatRequest extends Model{
    protected $table = 'wechat_request_message';
    protected $fillable = ['to_user_name', 'from_user_name', 'create_time','msg_type','content','pic_url','key'];

} 