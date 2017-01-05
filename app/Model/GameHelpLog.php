<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21 0021
 * Time: 上午 11:14
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class GameHelpLog extends Model{
    protected $table="game_help_log";
    protected $fillable = ['id','kid','openid','to_openid'];
    public function WechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
} 