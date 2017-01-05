<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/14 0014
 * Time: 下午 6:12
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class WechatCardList extends Model{
    protected $table = 'wechat_card_list';
    protected $fillable = ['id', 'openid', 'cardid','key','status','card_code'];

} 