<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/20 0020
 * Time: 上午 11:14
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class QiyeItemPollLog extends Model{

    protected $table = 'qiye_item_poll_log';
    protected $fillable =['id','item_id','ip'];

} 