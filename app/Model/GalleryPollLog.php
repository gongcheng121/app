<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/11 0011
 * Time: 下午 5:34
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class GalleryPollLog extends Model{
    protected $table="gallery_poll_log";
    protected $fillable = ['id','openid'];

} 