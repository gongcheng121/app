<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/5 0005
 * Time: 下午 12:10
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WifiArea extends Model {
    protected $table = 'wifi_info';
    protected $fillable = ['name', 'location', 'category','area'];
}