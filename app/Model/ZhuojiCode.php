<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class ZhuojiCode extends Model
{
    //
    protected $table = 'game_zhuoji_code';
    protected $fillable =['id','name','mobile','openid','score','code'];

    public function getMobileAttribute($val){
        return Helper::hideStr($val,3,5);
    }
    public function getNameAttribute($val){
        return Helper::hideStr($val,1);
    }
}
