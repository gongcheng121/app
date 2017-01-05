<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriftBottleGift extends Model
{
    //
    public $table = 'activity_drift_bottle_gifts';
    protected $fillable = [
        'name', 'count', 'v',];
}
