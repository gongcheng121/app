<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class LiveHostd extends Model implements Transformable
{
    use TransformableTrait;

    public $table='live_hostds';
    protected $fillable = [
        'lid', 'content', 'host', 'type'
    ];

    public function getContentAttribute($value){
//        $value = str_replace('style','style1',$value);

        $pattern = '/<img.+src=\"?.+.+\/(.+.).jpg|gif|bmp|bnp|png\"?.+>/i';
        preg_match($pattern,$value,$match);
        if(isset($match[1])) $value = str_replace($match[1],'thumb_'.$match[1],$value);
        return $value;
    }

}
