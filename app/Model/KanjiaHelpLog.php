<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/3 0003
 * Time: 下午 2:23
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class KanjiaHelpLog extends Model {
    protected $table="kanjia_help_log";
    protected $fillable = ['id','kid','openid','to_openid','money'];
    public function WechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
} 