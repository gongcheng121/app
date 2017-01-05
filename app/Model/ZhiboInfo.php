<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/5 0005
 * Time: 下午 12:10
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ZhiboInfo extends Model
{
    protected $table = 'zhibo_info';
    protected $fillable = [

        'zid',
        'from',
        'content',

    ];

    
}