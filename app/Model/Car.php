<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    //
    public $connection='sqlsrv';
    public $table='车辆档案';
    public $timestamps = false;



   
}
