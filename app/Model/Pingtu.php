<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/3 0003
 * Time: 上午 11:49
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Pingtu extends Model{

    protected $table = 'pingtu';
    protected $fillable = ['id', 'openid','prizeId'];

}