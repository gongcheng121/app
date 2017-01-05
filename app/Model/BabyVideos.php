<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;


class BabyVideos extends Model implements Transformable
{
    //
    use TransformableTrait;
    protected $table = 'baby_videos';
    protected $fillable = [
        'baby_info_id', 'video_url', 'video_local_url'
    ];


}
