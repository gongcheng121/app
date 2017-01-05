<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19 0019
 * Time: 上午 11:32
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class QiyeItem extends Model{
    protected $table = 'qiye_item';
    protected $fillable =['id','cat_id','type_id','name','listorder','description','image','thumb','link'];

    public function Category(){
        return $this->hasOne('App\Model\QiyeCategory','id','cat_id');
    }
    public function Type(){
        return $this->hasOne('App\Model\QiyeType','id','type_id');
    }

    public function count(){
        return $this->hasOne('App\Model\QiyeItemPoll','id','id');
    }
} 