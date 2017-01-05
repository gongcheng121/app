<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriftBottleMyGift extends Model
{
    //
    public $table='activity_drift_bottle_my_gifts';
    protected $fillable=['openid','gift_id','name','status','code'];
}
