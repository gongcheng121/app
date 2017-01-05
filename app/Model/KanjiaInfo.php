<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/3 0003
 * Time: 下午 2:21
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

/**
 * Class KanjiaInfo
 * @package App\Model
 */
class KanjiaInfo extends Model{
    protected $table="kanjia_info";
    protected $fillable = ['id','openid','help_count'];

    public function WechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
} 