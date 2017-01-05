<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class GuangDianPrize extends Model implements Transformable
{
    use TransformableTrait;


    public $table = 'activity_guangdian_prizes';
    protected $fillable = [

        'name', 'count', 'v',
    ]; 
}
