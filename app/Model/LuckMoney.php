<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/28 0028
 * Time: 下午 3:04
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class LuckMoney  extends Model{
    protected $table = 'wechat_luck_money';
    protected $fillable = ["send_name","re_openid","total_amount","wishing","act_name","remark","key",'type'];

} 