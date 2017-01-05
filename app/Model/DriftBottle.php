<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class DriftBottle extends Model implements Transformable
{
    use TransformableTrait;
    public $table = 'activity_drift_bottles';
    protected $fillable = [
'openid',
'hope',
'status',


    ];

    public function wechatMember(){
        return $this->hasOne('App\Model\WechatMember','openid','openid');
    }
}
