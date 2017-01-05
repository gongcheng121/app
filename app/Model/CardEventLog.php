<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21 0021
 * Time: 下午 3:53
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class CardEventLog extends Model {
    protected $table="card_event_log";
    protected $fillable = ['to_user_name','from_user_name','friend_user_name','create_time','msg_type','card_id','user_card_code','is_give_by_friend','outer_id','event','key'];
} 