<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GuozhiyuanVotePollLog extends Model
{
    //
    public $table = 'activity_guozhiyuan_votes_poll_log';
    protected $fillable = [ 
        'vote_id', 'ip', 'openid',
    ];
}
