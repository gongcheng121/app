<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriftBottleCard extends Model
{
    //
    public $table = 'activity_drift_bottle_cards';

    protected $fillable = [
        'openid', 'type_id', 'type', 'status','count'
    ];
}
