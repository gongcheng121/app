<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class BankVotePoll extends Model implements Transformable
{
    use TransformableTrait;

    public $table='bank_vote_polls';
    protected $fillable = ['vote_id','count'];

}
