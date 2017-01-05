<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/12 0012
 * Time: 上午 11:44
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class VideoInfo extends Model{
    protected $table = 'video_info';
    protected $fillable = ['id', 'name', 'file','mobile','beizhu'];

} 