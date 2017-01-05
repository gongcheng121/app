<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Traits\TransformableTrait;

class GuangdianCode extends Model
{
    //
    use TransformableTrait;


    public $table = 'activity_guangdian_code';
    protected $fillable = [

        'gid', 'status','type'
    ];

    
}
