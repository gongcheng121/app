<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19 0019
 * Time: 下午 6:07
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Qrwz extends Model{

    protected $table = 'qrwz';
    protected $fillable =['id','name','link','mobile_link','count'];

} 