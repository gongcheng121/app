<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class BabyVotePoll extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = [
        'vote_id',
        'count',

    ];

}
