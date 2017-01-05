<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GuozhiyuanVotePoll extends Model
{
    //
    public $table='activity_guozhiyuan_votes_poll';
    protected $fillable = ['id','vote_id','count'];
}
