<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19 0019
 * Time: 上午 11:29
 */

namespace App\Model;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class QiyeUser extends Model{


    protected $table = 'qiye_poll_user';
    protected $fillable =['id','name','mobile'];

}