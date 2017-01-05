<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class BabyInfo extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'baby_info';
    protected $fillable = [

        'baby_name', 'baby_words', 'birthday', 'father_name', 'father_mobile', 'father_qq', 'father_wechat', 'mother_name', 'mother_mobile', 'mother_qq', 'mother_wechat',];

    public function video(){
        return $this->hasMany('App\Model\BabyVideos','baby_info_id');
    }
}
