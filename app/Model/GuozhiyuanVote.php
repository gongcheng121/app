<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class GuozhiyuanVote extends Model implements Transformable
{
    use TransformableTrait;

    public $table = 'activity_guozhiyuan_votes';
    protected $fillable = [
        'openid', 'name', 'mobile', 'image', 'description', 'list_order'
    ];

    public function count(){
        return $this->hasOne('App\Model\GuozhiyuanVotePoll','vote_id','id');
    }

}
