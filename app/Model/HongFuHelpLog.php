<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class HongFuHelpLog extends Model implements Transformable
{
    use TransformableTrait;
    public $table = 'activity_hongfu_help_logs';

    protected $fillable = [

        'openid', 'gift_id', 'to_openid', 'created_at', 'updated_at',

    ];

    public function wechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }

}
