<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class BankVotePollLog extends Model implements Transformable
{
    use TransformableTrait;
    public $table='bank_vote_poll_log';
    protected $fillable = [
        'vote_id', 'ip', 'open_id'
    ];

}
