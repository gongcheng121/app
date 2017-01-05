<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19 0019
 * Time: 上午 11:29
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class QiyeCategory extends Model{


    protected $table = 'qiye_category';
    protected $fillable =['id','cat_name','type_id','listorder','description'];


    public function Item(){
        return $this->hasMany('App\Model\QiyeItem','cat_id','id');
    }

    public function Type(){
        return $this->hasOne('App\Model\QiyeType','id','type_id');
    }
}