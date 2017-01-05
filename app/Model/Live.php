<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Live extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = [
        'title', 'images', 'start_time', 'description', 'type', 'organizers','views'
    ];

    public function live_hostds(){
        return $this->hasMany('App\Model\LiveHostd','lid');
    }
}
