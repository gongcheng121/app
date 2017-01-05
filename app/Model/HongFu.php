<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class HongFu extends Model implements Transformable
{
    use TransformableTrait;
    public $table = 'activity_hongfus';
    protected $fillable = [

        'openid', 'prize', 'prize_id', 'help_counts', 'status'
    ];

    public function help_logs(){
        return $this->hasMany('App\Model\HongFuHelpLog','gift_id','id');
    }

    public function wechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
}
