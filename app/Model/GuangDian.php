<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class GuangDian extends Model implements Transformable
{
    use TransformableTrait;

    public $table = 'activity_guangdians';
    protected $fillable = [

        'openid', 'prize', 'prize_id', 'help_counts', 'status'
    ];

    public function help_logs(){
        return $this->hasMany('App\Model\GuangDianHelpLog','gift_id','id');
    }

    public function code(){
        return $this->hasMany('App\Model\GuangdianCode','gid','id');
    }

    public function wechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }

}
