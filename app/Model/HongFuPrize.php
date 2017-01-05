<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class HongFuPrize extends Model implements Transformable
{
    use TransformableTrait;

    public $table = 'activity_hongfu_prizes';
    protected $fillable = [

        'name', 'count', 'v', 
    ];

}
