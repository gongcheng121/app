<?php
/**
 * Created by PhpStorm.
 * User: koala
 * Date: 02/02/16
 * Time: 下午 03:55
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class VideoPoll extends Model
{
    protected $table="activity_video_poll_list";
    protected $fillable = ['video_name','src','count','video_desc','remark','thumb'];
}