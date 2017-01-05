<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21 0021
 * Time: 上午 11:13
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class GameHelpInfo extends Model{

    protected $table="game_help_info";
    protected $fillable = ['id','openid','help_count'];

    public function WechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
} 