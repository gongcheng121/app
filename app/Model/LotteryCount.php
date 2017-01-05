<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 15-7-22
 * Time: 下午1:46
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class LotteryCount extends Model {
    protected $table = 'wechat_lottery_count';
    protected $fillable = ['prize', 'count','prizeId'];
}