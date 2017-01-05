<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/9 0009
 * Time: 下午 4:54
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Cron extends Model {
    protected $table="cron";
    protected $fillable = ['type'];
} 