<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class DaheVote extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = [
        'count', 'title', 'name', 'teacher', 'type', 'file', 'thumb', 'status'

    ];

    public function poll(){
        return $this->hasOne('App\Model\DaheVotePoll','vote_id','id');
    }

}
