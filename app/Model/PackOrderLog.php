<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/30 0030
 * Time: 下午 6:44
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class PackOrderLog extends Model{
    protected $table = 'wechat_pack_order_log';
    protected $fillable = ['mch_billno', 'mch_id','return_code','return_msg'];
} 