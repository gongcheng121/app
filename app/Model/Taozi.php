<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/25 0025
 * Time: 上午 11:39
 */

namespace App\Model;


use App\Services\Helper;
use Illuminate\Database\Eloquent\Model;

class Taozi extends Model{

    protected $table = 'game_taozi';
    protected $fillable =['id','name','mobile','score'];

    public function getMobileAttribute($val){
        return Helper::hideStr($val,3,5);
    }
    public function getNameAttribute($val){
        return Helper::hideStr($val,1);
    }

} 