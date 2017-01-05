<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class BabyVote extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = [

        'type',
        'title',
        'video_url',
        'images',
        'description',


    ];

    public function poll(){
        return $this->hasOne('App\Model\BabyVotePoll','vote_id');
    }
}
