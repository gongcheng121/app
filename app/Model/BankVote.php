<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class BankVote extends Model implements Transformable
{
    use TransformableTrait;
    public $table = 'activity_bank_votes';
    protected $fillable = [

        'openid', 'name', 'mobile', 'image', 'title', 'list_order','status',
    ];

    public function vote_count(){
        return $this->hasOne('App\Model\BankVotePoll','vote_id','id');
    }

}
