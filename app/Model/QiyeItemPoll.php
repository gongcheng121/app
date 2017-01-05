<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/20 0020
 * Time: 上午 11:08
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class QiyeItemPoll extends Model{
    protected $table = 'qiye_item_poll';
    protected $fillable =['id','count'];

    public function getCountAttribute($value){
        if(!$value){
            return 1000;
        }else if($value<1000){
            return $value+1000;
        }
        return $value;
    }
}